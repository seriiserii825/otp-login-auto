<?php

if (!defined('ABSPATH')) exit;

/**
 * Cron handler: delete an OTP-created user after the configured TTL.
 */
add_action('eol_delete_user', function (int $user_id): void {
  require_once ABSPATH . 'wp-admin/includes/user.php';
  wp_delete_user($user_id);
});
