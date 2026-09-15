<?php

return [
    // Page titles
    'title' => 'Bildirimlerim',
    'my_notifications' => 'Bildirimlerim',

    // Actions
    'mark_all_read' => 'Tümünü Okundu İşaretle',
    'mark_read' => 'Okundu İşaretle',
    'mark_unread' => 'Okunmadı İşaretle',
    'delete' => 'Sil',
    'view_all' => 'Tüm Bildirimleri Görüntüle',

    // States
    'read' => 'Okundu',
    'unread' => 'Okunmadı',
    'important' => 'Önemli',
    'new' => 'Yeni',

    // Messages
    'no_notifications' => 'Henüz bildiriminiz yok',
    'no_notifications_desc' => 'Yeni bildirimler geldiğinde burada görüntülenir.',
    'no_new_notifications' => 'Yeni bildiriminiz yok',
    'unread_count' => 'okunmamış bildirim',

    // Actions messages
    'marked_as_read' => 'Bildirim okundu olarak işaretlendi.',
    'marked_as_unread' => 'Bildirim okunmadı olarak işaretlendi.',
    'all_marked_as_read' => 'Tüm bildirimler okundu olarak işaretlendi.',
    'notification_deleted' => 'Bildirim silindi.',
    'notifications_deleted' => 'bildirim silindi.',

    // Confirmation messages
    'confirm_delete' => 'Bu bildirimi silmek istediğinizden emin misiniz?',
    'confirm_bulk_delete' => 'Seçilen bildirimleri silmek istediğinizden emin misiniz?',

    // Error messages
    'error_occurred' => 'Bir hata oluştu.',
    'failed_to_load' => 'Bildirimler yüklenemedi.',
    'failed_to_mark_read' => 'Bildirim okundu işaretlenemedi.',
    'failed_to_delete' => 'Bildirim silinemedi.',

    // Time
    'time_ago' => [
        'just_now' => 'Az önce',
        'minutes_ago' => 'dakika önce',
        'hours_ago' => 'saat önce',
        'days_ago' => 'gün önce',
        'weeks_ago' => 'hafta önce',
        'months_ago' => 'ay önce',
        'years_ago' => 'yıl önce',
    ],

    // Notification content templates
    'listing_approved_title' => 'İlanınız Onaylandı!',
    'listing_approved_message' => '":title" başlıklı ilanınız onaylandı ve yayınlandı.',

    'listing_rejected_title' => 'İlan Onaylanmadı',
    'listing_rejected_message' => '":title" başlıklı ilanınız onaylanmadı.',
    'listing_rejected_with_reason' => '":title" başlıklı ilanınız onaylanmadı. Sebep: :reason',

    'listing_submitted_title' => 'İlanınız Onaya Gönderildi',
    'listing_submitted_message' => '":title" başlıklı ilanınız başarıyla oluşturuldu ve yönetici onayına gönderildi. Onaylandığında bildirim alacaksınız.',

    'listing_resubmitted_title' => 'İlan Değişiklikleriniz Onaya Gönderildi',
    'listing_resubmitted_message' => '":title" başlıklı ilanınızda yaptığınız değişiklikler kaydedildi ve tekrar yönetici onayına gönderildi. Onaylanana kadar ilan eski haliyle yayında kalacaktır.',

    'listing_resubmitted_admin_title' => 'İlan Düzenlendi ve Yeniden Onay Bekliyor',
    'listing_resubmitted_admin_message' => ':user kullanıcısı ":title" başlıklı ilanını düzenledi. İlan tekrar onayınızı bekliyor.',

    'listing_expired_title' => 'İlan Süresi Doldu ve Pasife Alındı',
    'listing_expired_message' => '":title" başlıklı ilanınızın 30 günlük yayın süresi doldu ve paket hakkınız kalmadığı için yayından kaldırıldı. Tekrar yayınlamak için paket satın alabilirsiniz.',

    'listing_renewed_title' => 'İlanınız Otomatik Yenilendi',
    'listing_renewed_message' => '":title" başlıklı ilanınızın süresi dolmuştu ve otomatik olarak 30 gün daha uzatıldı. Yeni bitiş tarihi: :expires_at',

    'new_message_title' => 'Yeni Mesaj',
    'new_message_content' => ':sender size ":listing" ilanınız hakkında mesaj gönderdi.',

    // Action buttons
    'view_listing' => 'İlanı Görüntüle',
    'edit_listing' => 'İlanı Düzenle',
    'my_listings' => 'İlanlarım',
    'view_message' => 'Mesajı Görüntüle',
    'all_messages' => 'Tüm Mesajlar',
    'renew_listing' => 'İlanı Yenile',
    'buy_package' => 'Paket Satın Al',
    'my_package' => 'Paketim',

    // Page description
    'page_description' => 'Tüm bildirimlerinizi burada görüntüleyebilirsiniz',

    // Navigation
    'notifications_menu' => 'Bildirimlerim',
];
