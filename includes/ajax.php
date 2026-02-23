<?php

if (!defined('ABSPATH')) exit;

/**
 * AJAX: send OTP to the provided email.
 * Creates a new WP user if one doesn't exist yet.
 */
add_action('wp_ajax_nopriv_eol_send_otp', 'eol_send_otp_ajax');

function eol_send_otp_ajax(): void
{
  check_ajax_referer('eol_nonce', 'nonce');

  $email = sanitize_email($_POST['email']);
  if (!$email) wp_send_json_error('Invalid email');

  $domain = substr(strrchr($email, '@'), 1);
  if (!in_array($domain, eol_allowed_domains(), true)) {
    wp_send_json_error('Domain not allowed');
  }

  $user = get_user_by('email', $email);
  if (!$user) {
    $user_id = wp_create_user($email, wp_generate_password(), $email);
    $user    = get_user_by('id', $user_id);
  }

  $otp = eol_generate_otp();

  update_user_meta($user->ID, 'eol_otp',     $otp);
  $ttl = (int) get_option('eol_otp_ttl', 5) * 60;
  update_user_meta($user->ID, 'eol_otp_exp', time() + $ttl);

  // remove wp_send_json_error to prevent revealing if email exists


  eol_send_otp($email, $otp);

  wp_send_json_success();
}

/**
 * AJAX: verify the submitted OTP and log the user in.
 */
add_action('wp_ajax_nopriv_eol_verify_otp', 'eol_verify_otp_ajax');

function eol_verify_otp_ajax(): void
{
  check_ajax_referer('eol_nonce', 'nonce');

  $otp = sanitize_text_field($_POST['otp']);

  $users = get_users([
    'meta_key'   => 'eol_otp',
    'meta_value' => $otp,
    'number'     => 1,
  ]);

  if (!$users) wp_send_json_error('Invalid OTP');

  $user = $users[0];
  $exp  = (int) get_user_meta($user->ID, 'eol_otp_exp', true);

  if (time() > $exp) wp_send_json_error('OTP expired');

  delete_user_meta($user->ID, 'eol_otp');
  delete_user_meta($user->ID, 'eol_otp_exp');

  wp_set_auth_cookie($user->ID);
  wp_send_json_success(['redirect' => home_url()]);
}
