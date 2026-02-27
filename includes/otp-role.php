<?php
// 1️⃣ Создаем кастомную роль
function add_otp_role()
{
  if (!get_role('otp')) {
    add_role(
      'otp',       // slug роли
      'Otp',       // название роли
      [
        'read' => true,    // базовое право доступа к сайту
      ]
    );
  }
}
add_action('init', 'add_otp_role');


// 2️⃣ Массив ID страниц, доступных кастомной роли
function get_custom_pages_for_viewer(): array
{
  $raw = get_option('eol_allowed_pages', '');
  if ($raw === '') return [];
  return array_map('intval', explode(',', $raw));
}


// 3️⃣ Ограничиваем доступ к указанным страницам
function restrict_custom_pages_access()
{
  if (is_page()) {
    $allowed_pages = get_custom_pages_for_viewer();
    $current_page_id = get_queried_object_id();

    if (in_array($current_page_id, $allowed_pages)) {
      $user = wp_get_current_user();

      // Доступ есть только у админа или otp
      if (!in_array('otp', (array) $user->roles) && !current_user_can('manage_options')) {
        wp_redirect(home_url()); // редирект на главную
        exit;
      }
    }
  }
}
add_action('template_redirect', 'restrict_custom_pages_access');
