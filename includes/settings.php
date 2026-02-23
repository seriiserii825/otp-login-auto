<?php

if (!defined('ABSPATH')) exit;

/**
 * Register the settings page under Settings > OTP Login.
 */
add_action('admin_menu', function () {
  add_options_page(
    'OTP Login Settings',
    'OTP Login',
    'manage_options',
    'eol-settings',
    'eol_render_settings_page'
  );
});

/**
 * Register the options and their sanitization callbacks.
 */
add_action('admin_init', function () {
  register_setting('eol_settings_group', 'eol_domains', [
    'sanitize_callback' => 'eol_sanitize_domains',
    'default'           => '',
  ]);

  register_setting('eol_settings_group', 'eol_otp_ttl', [
    'sanitize_callback' => 'eol_sanitize_ttl',
    'default'           => 5,
  ]);

  register_setting('eol_settings_group', 'eol_user_ttl', [
    'sanitize_callback' => 'eol_sanitize_ttl',
    'default'           => 1,
  ]);
});

/**
 * Sanitize TTL: integer, minimum 1 minute.
 */
function eol_sanitize_ttl($raw): int
{
  $value = (int) $raw;
  return max(1, $value);
}

/**
 * Sanitize: trim lines, remove empty ones, lowercase.
 */
function eol_sanitize_domains(string $raw): string
{
  $lines = explode("\n", $raw);
  $clean = [];

  foreach ($lines as $line) {
    $domain = strtolower(trim($line));
    if ($domain !== '') {
      $clean[] = $domain;
    }
  }

  return implode("\n", array_unique($clean));
}

/**
 * Render the settings page.
 */
function eol_render_settings_page(): void
{
  if (!current_user_can('manage_options')) return;
  ?>
  <div class="wrap">
    <h1>OTP Login Settings</h1>

    <form method="post" action="options.php">
      <?php settings_fields('eol_settings_group'); ?>

      <h2>Allowed Domains</h2>
      <p>One domain per line. Only users with these email domains will be able to log in.</p>
      <textarea
        name="eol_domains"
        rows="10"
        cols="40"
        placeholder="example.com&#10;company.org"
        style="font-family: monospace;"
      ><?php echo esc_textarea(get_option('eol_domains', '')); ?></textarea>

      <h2>OTP Expiration</h2>
      <p>How long the OTP code remains valid after sending.</p>
      <label>
        <input
          type="number"
          name="eol_otp_ttl"
          value="<?php echo esc_attr(get_option('eol_otp_ttl', 5)); ?>"
          min="1"
          style="width: 80px;"
        /> minutes
      </label>

      <h2>User Auto-Deletion</h2>
      <p>Automatically delete users created via OTP login after this time.</p>
      <label>
        <input
          type="number"
          name="eol_user_ttl"
          value="<?php echo esc_attr(get_option('eol_user_ttl', 1)); ?>"
          min="1"
          style="width: 80px;"
        /> minutes
      </label>

      <?php submit_button('Save settings'); ?>
    </form>
  </div>
  <?php
}
