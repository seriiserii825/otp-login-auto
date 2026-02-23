# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Plugin Does

WordPress plugin that replaces the standard password login with a passwordless email OTP flow. Users enter their email, receive a 6-digit code via `wp_mail`, and log in by submitting that code. Domain restriction is enforced server-side — only emails from allowed domains may log in. New WP users are auto-created on first login.

## Architecture

All logic lives in `includes/`, loaded sequentially from `email-otp-login.php`:

| File | Responsibility |
|------|---------------|
| `includes/config.php` | `eol_allowed_domains()` — reads `eol_domains` WP option |
| `includes/settings.php` | Admin page at Settings > OTP Login; registers/sanitizes `eol_domains` option |
| `includes/otp.php` | `eol_generate_otp()` (6-digit, `random_int`) and `eol_send_otp()` (wraps `wp_mail`) |
| `includes/ajax.php` | Two `wp_ajax_nopriv_` handlers: `eol_send_otp` and `eol_verify_otp` |
| `includes/shortcode.php` | `[email_otp_login]` shortcode — renders two-step form and enqueues `assets/otp.js` |
| `assets/otp.js` | jQuery: posts to `admin-ajax.php`, swaps email/OTP forms, redirects on success |

## Key Conventions

- **Prefix**: all functions, hooks, and WP options use `eol_`.
- **OTP storage**: stored in user meta — `eol_otp` (the code) and `eol_otp_exp` (Unix timestamp, +300 s). Both are deleted immediately after a successful verify.
- **Nonce**: `eol_nonce` — created in the shortcode via `wp_localize_script` and checked in both AJAX handlers with `check_ajax_referer`.
- **EOL_PLUGIN_FILE**: constant defined in the main file, used in `shortcode.php` to resolve the asset URL.

## Development Notes

No build step or Composer — plain PHP and vanilla jQuery (loaded as a WP dependency). To develop locally this plugin runs inside the Local by Flywheel site at `/home/serii/Local Sites/lc-otp/`.

To test the login flow manually: place `[email_otp_login]` on any page, ensure at least one domain is saved under Settings > OTP Login, and submit an email with that domain.

AJAX endpoints (both `nopriv`):
- `eol_send_otp` — validates domain, creates user if needed, stores OTP meta, sends email
- `eol_verify_otp` — looks up user by `eol_otp` meta value, checks expiry, sets auth cookie
