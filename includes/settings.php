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

  register_setting('eol_settings_group', 'eol_user_role', [
    'sanitize_callback' => 'eol_sanitize_role',
    'default'           => 'subscriber',
  ]);

  register_setting('eol_settings_group', 'eol_allowed_pages', [
    'sanitize_callback' => 'eol_sanitize_page_ids',
    'default'           => '',
  ]);
});

/**
 * Sanitize page IDs: comma-separated integers.
 */
function eol_sanitize_page_ids(string $raw): string
{
  $ids = array_map('intval', explode(',', $raw));
  $ids = array_filter($ids, fn($id) => $id > 0);
  return implode(',', array_unique($ids));
}

/**
 * Sanitize role: must be a valid WP role slug.
 */
function eol_sanitize_role(string $raw): string
{
  $valid = array_keys(wp_roles()->roles);
  $slug  = sanitize_key($raw);
  return in_array($slug, $valid, true) ? $slug : 'subscriber';
}

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

      <h2>New User Role</h2>
      <p>Role assigned to users created automatically via OTP login.</p>
      <select name="eol_user_role">
        <?php
        $saved = get_option('eol_user_role', 'subscriber');
        foreach (wp_roles()->roles as $slug => $data) {
          printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($slug),
            selected($saved, $slug, false),
            esc_html($data['name'])
          );
        }
        ?>
      </select>

      <h2>OTP Role Allowed Pages</h2>
      <p>Page IDs accessible to the <code>otp</code> role. Comma-separated, e.g. <code>42, 55, 101</code>.</p>
      <input
        type="text"
        name="eol_allowed_pages"
        value="<?php echo esc_attr(get_option('eol_allowed_pages', '')); ?>"
        style="width: 300px;"
        placeholder="42, 55, 101"
      />

      <?php submit_button('Save settings'); ?>
    </form>

    <h2>Usage</h2>
    <p>Place this shortcode on any page where you want the login form to appear:</p>
    <code style="display:inline-block; padding: 6px 10px; background: #f0f0f1; font-size: 14px;">[email_otp_login]</code>
  </div>
  <?php
}
