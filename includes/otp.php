<?php

if (!defined('ABSPATH')) exit;

/**
 * Generate a 6-digit OTP code.
 */
function eol_generate_otp(): string
{
  return (string) random_int(100000, 999999);
}

/**
 * Send OTP code to the given email address.
 */
function eol_send_otp(string $email, string $otp): void
{
  wp_mail(
    $email,
    'Your login code',
    "Your OTP code is: {$otp}\nValid for 5 minutes."
  );
}
