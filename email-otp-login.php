<?php

/**
 * Plugin Name: Email OTP Login Custom (Passwordless)
 * Description: Passwordless login via email + OTP with domain restriction
 * Name: email-otp-login-custom
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Allowed domains
 */
function eol_allowed_domains(): array
{
  return [
    'lc-otp.local',
    'bludelego.it',
    'company.com',
    'example.org',
  ];
}

/**
 * Generate OTP
 */
function eol_generate_otp(): string
{
  return (string) random_int(100000, 999999);
}

/**
 * Send OTP email
 */
function eol_send_otp($email, $otp)
{
  wp_mail(
    $email,
    'Your login code',
    "Your OTP code is: {$otp}\nValid for 5 minutes."
  );
}

/**
 * Shortcode: [email_otp_login]
 */
add_shortcode('email_otp_login', function () {
  wp_enqueue_script(
    'email-otp-login',
    plugin_dir_url(__FILE__) . 'assets/otp.js',
    ['jquery'],
    null,
    true
  );

  wp_localize_script('email-otp-login', 'EOL', [
    'ajax' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('eol_nonce'),
  ]);

  ob_start(); ?>

  <form id="eol-email-form">
    <input type="email" name="email" placeholder="Email" required>
    <button>Send code</button>
  </form>

  <form id="eol-otp-form" style="display:none">
    <input type="text" name="otp" placeholder="OTP code" required>
    <button>Login</button>
  </form>

  <div id="eol-msg"></div>

<?php
  return ob_get_clean();
});

add_action('wp_ajax_nopriv_eol_send_otp', 'eol_send_otp_ajax');

function eol_send_otp_ajax()
{
  check_ajax_referer('eol_nonce', 'nonce');

  $email = sanitize_email($_POST['email']);
  if (!$email) wp_send_json_error('Invalid email');

  // 🔒 Domain restriction
  $domain = substr(strrchr($email, '@'), 1);
  if (!in_array($domain, eol_allowed_domains(), true)) {
    wp_send_json_error('Domain not allowed');
  }

  $user = get_user_by('email', $email);
  if (!$user) {
    $user_id = wp_create_user($email, wp_generate_password(), $email);
    $user = get_user_by('id', $user_id);
  }

  $otp = eol_generate_otp();

  update_user_meta($user->ID, 'eol_otp', $otp);
  update_user_meta($user->ID, 'eol_otp_exp', time() + 300);

  eol_send_otp($email, $otp);

  wp_send_json_success();
}

add_action('wp_ajax_nopriv_eol_verify_otp', 'eol_verify_otp_ajax');

function eol_verify_otp_ajax()
{
  check_ajax_referer('eol_nonce', 'nonce');

  $otp = sanitize_text_field($_POST['otp']);

  $users = get_users([
    'meta_key' => 'eol_otp',
    'meta_value' => $otp,
    'number' => 1,
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
