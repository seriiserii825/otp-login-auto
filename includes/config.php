<?php

if (!defined('ABSPATH')) exit;

/**
 * Returns the list of allowed email domains.
 * Managed via Settings > OTP Login in the admin panel.
 */
function eol_allowed_domains(): array
{
  $raw = get_option('eol_domains', '');
  if ($raw === '') return [];

  return array_filter(
    array_map('trim', explode("\n", $raw))
  );
}
