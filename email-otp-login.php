<?php

/**
 * Plugin Name: OTP Login Auto
 * Description: Passwordless login via email + OTP with domain restriction
 * Version: 1.0
 * Author: serii
 */

if (!defined('ABSPATH')) exit;

define('EOL_PLUGIN_FILE', __FILE__);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  $settings_link = '<a href="' . admin_url('options-general.php?page=eol-settings') . '">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
});

require_once __DIR__ . '/includes/otp-role.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/otp.php';
require_once __DIR__ . '/includes/shortcode.php';
require_once __DIR__ . '/includes/ajax.php';
require_once __DIR__ . '/includes/cleanup.php';
