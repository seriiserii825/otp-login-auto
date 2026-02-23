<?php

/**
 * Plugin Name: Email OTP Login Custom (Passwordless)
 * Description: Passwordless login via email + OTP with domain restriction
 * Name: email-otp-login-custom
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

define('EOL_PLUGIN_FILE', __FILE__);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/otp.php';
require_once __DIR__ . '/includes/shortcode.php';
require_once __DIR__ . '/includes/ajax.php';
