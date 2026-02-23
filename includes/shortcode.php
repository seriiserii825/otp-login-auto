<?php

if (!defined('ABSPATH')) exit;

/**
 * Shortcode [email_otp_login]
 * Renders the email + OTP login forms.
 */
add_shortcode('email_otp_login', function () {
  wp_enqueue_script(
    'email-otp-login',
    plugin_dir_url(EOL_PLUGIN_FILE) . 'assets/otp.js',
    ['jquery'],
    null,
    true
  );

  wp_localize_script('email-otp-login', 'EOL', [
    'ajax'  => admin_url('admin-ajax.php'),
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
