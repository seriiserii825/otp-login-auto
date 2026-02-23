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
 * Register the option and its sanitization callback.
 */
add_action('admin_init', function () {
  register_setting('eol_settings_group', 'eol_domains', [
    'sanitize_callback' => 'eol_sanitize_domains',
    'default'           => '',
  ]);
});

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
    <h1>OTP Login — Allowed Domains</h1>
    <p>One domain per line. Only users with these email domains will be able to log in.</p>

    <form method="post" action="options.php">
      <?php settings_fields('eol_settings_group'); ?>

      <textarea
        name="eol_domains"
        rows="10"
        cols="40"
        placeholder="example.com&#10;company.org"
        style="font-family: monospace;"
      ><?php echo esc_textarea(get_option('eol_domains', '')); ?></textarea>

      <?php submit_button('Save domains'); ?>
    </form>
  </div>
  <?php
}
