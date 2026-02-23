<?php

if (!defined('ABSPATH')) exit;

/**
 * Allowed domains for OTP login.
 * Add or remove domains here to control who can log in.
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
