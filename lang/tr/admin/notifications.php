<?php

return [
    // Page titles
    'title' => 'Bildirimler',
    'create_title' => 'Yeni Bildirim Gönder',

    // Actions
    'send_notification' => 'Yeni Bildirim Gönder',
    'mark_all_read' => 'Tümünü Okundu İşaretle',
    'mark_selected_read' => 'Seçilileri Okundu İşaretle',
    'mark_selected_unread' => 'Seçilileri Okunmadı İşaretle',
    'delete_selected' => 'Seçilileri Sil',
    'send' => 'Bildirimi Gönder',
    'cancel' => 'İptal',

    // Form fields
    'target' => 'Gönderim Hedefi',
    'target_admin' => 'Admin (Kendim)',
    'target_user' => 'Belirli Kullanıcı',
    'target_all_users' => 'Tüm Kullanıcılar',
    'select_user' => 'Kullanıcı Seç',
    'select_target' => 'Hedef seçin...',
    'select_user_placeholder' => 'Kullanıcı seçin...',
    'title_field' => 'Başlık',
    'message_field' => 'Mesaj',
    'important' => 'Önemli Bildirim',
    'custom_icon' => 'Özel İkon (İsteğe bağlı)',
    'action_links' => 'Eylem Bağlantıları (İsteğe bağlı)',
    'link_title' => 'Bağlantı başlığı',
    'link_url' => 'URL',
    'link_type' => 'Tip',
    'link_primary' => 'Birincil',
    'link_secondary' => 'İkincil',
    'link_danger' => 'Tehlike',
    'add_link' => 'Bağlantı Ekle',

    // Table headers
    'status' => 'Durum',
    'type' => 'Tür',
    'sender' => 'Gönderen',
    'date' => 'Tarih',
    'actions' => 'İşlemler',

    // Statuses
    'read' => 'Okundu',
    'unread' => 'Okunmadı',
    'important_label' => 'Önemli',
    'new_label' => 'Yeni',

    // Stats
    'total_notifications' => 'Toplam Bildirim',
    'unread_notifications' => 'Okunmamış',

    // Messages
    'no_notifications' => 'Henüz bildirim yok',
    'no_notifications_desc' => 'Yeni bildirimler geldiğinde burada görüntülenir.',
    'notification_sent' => 'Bildirim başarıyla gönderildi.',
    'notifications_marked_read' => 'Bildirimler okundu olarak işaretlendi.',
    'notification_deleted' => 'Bildirim silindi.',
    'please_select_notifications' => 'Lütfen işlem yapmak için bildirimleri seçin.',
    'confirm_delete' => 'Bu bildirimi silmek istediğinizden emin misiniz?',
    'confirm_delete_selected' => 'Seçilen bildirimleri silmek istediğinizden emin misiniz?',
    'notification_immutable' => 'Bildirim gönderildikten sonra değiştirilemez.',

    // Validation messages
    'validation_title_required' => 'Başlık gereklidir.',
    'validation_message_required' => 'Mesaj gereklidir.',
    'validation_target_required' => 'Gönderim hedefi seçmelisiniz.',
    'validation_user_required_for_user_target' => 'Kullanıcı hedefi seçildiğinde kullanıcı belirtmelisiniz.',
    'validation_action_links_incomplete' => 'Eylem bağlantıları için hem başlık hem de URL doldurulmalıdır.',

    // Help texts
    'important_help' => 'Önemli bildirimler vurgulanarak gösterilir.',
    'icon_help' => 'Font Awesome sınıfı. Boş bırakılırsa bildirim türüne göre otomatik ikon seçilir.',
    'icon_examples' => 'Örnekler: fas fa-info-circle, fas fa-exclamation-triangle, fas fa-check-circle',
    'action_links_help' => 'Kullanıcıların bildirimden hızla işlem yapabilmesi için bağlantılar ekleyebilirsiniz.',

    // Notification types
    'type_listing_approved' => 'İlan Onaylandı',
    'type_listing_rejected' => 'İlan Reddedildi',
    'type_listing_expired' => 'İlan Süresi Doldu',
    'type_new_listing' => 'Yeni İlan',
    'type_new_report' => 'Yeni Şikayet',
    'type_new_message' => 'Yeni Mesaj',
    'type_custom' => 'Özel',

    // Breadcrumbs
    'breadcrumb_dashboard' => 'Dashboard',
    'breadcrumb_notifications' => 'Bildirimler',
    'breadcrumb_new_notification' => 'Yeni Bildirim',
];
