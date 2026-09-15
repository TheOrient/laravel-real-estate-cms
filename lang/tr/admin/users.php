<?php

return [
    // Titles
    'manage_users' => 'Kullanıcı Yönetimi',
    'manage_team' => 'Ekip ve Danışman Yönetimi',
    'all_users' => 'Tüm Kullanıcılar',
    'create_user' => 'Yeni Kullanıcı Oluştur',
    'create_agent' => 'Yeni Danışman Ekle',
    'edit_user' => 'Kullanıcı Düzenle',
    'user_details' => 'Kullanıcı Detayları',

    // Table columns
    'id' => 'ID',
    'first_name' => 'Ad',
    'last_name' => 'Soyad',
    'name' => 'Ad Soyad',
    'email' => 'E-posta',
    'role' => 'Rol',
    'phone' => 'Telefon',
    'enable_whatsapp' => 'WhatsApp Göster',
    'yes' => 'Evet',
    'no' => 'Hayır',
    'status' => 'Durum',
    'created_at' => 'Kayıt Tarihi',
    'actions' => 'İşlemler',

    // Form labels
    'user_role' => 'Kullanıcı Rolü',
    'password' => 'Şifre',
    'password_confirmation' => 'Şifre Tekrar',
    'is_active' => 'Aktif',
    'email_verified' => 'Email Onaylandı',
    'leave_blank' => 'Değiştirmek istemiyorsanız boş bırakın',

    // Roles
    'admin' => 'Yönetici',
    'user' => 'Kullanıcı',
    'agent' => 'Danışman',

    // Status
    'active' => 'Aktif',
    'inactive' => 'Pasif',
    'verified' => 'Doğrulanmış',
    'unverified' => 'Doğrulanmamış',

    // Alerts
    'created_successfully' => 'Kullanıcı başarıyla oluşturuldu.',
    'updated_successfully' => 'Kullanıcı başarıyla güncellendi.',
    'deleted_successfully' => 'Kullanıcı başarıyla silindi.',
    'moved_to_trash' => 'Kullanıcı çöpe taşındı.',
    'deleted_permanently' => 'Kullanıcı kalıcı olarak silindi.',
    'restored_successfully' => 'Kullanıcı başarıyla geri yüklendi.',
    'active_users' => 'Aktif Kullanıcılar',
    'trashed_users' => 'Çöpe Atılmış Kullanıcılar',
    'cannot_delete_self' => 'Kendinizi silemezsiniz.',
    'cannot_edit_super_admin' => 'Süper yönetici kullanıcısı düzenlenemez.',
    'cannot_delete_super_admin' => 'Süper yönetici kullanıcısı silinemez.',
    'cannot_change_super_admin_password' => 'Süper yönetici şifresi sadece kendisi tarafından değiştirilebilir.',
    'cannot_change_super_admin_role' => 'Süper yönetici rolü değiştirilemez.',
    'super_admin_protected' => 'Bu kullanıcı sistem tarafından korunmaktadır.',

    // Buttons
    'create' => 'Yeni Kullanıcı',
    'save' => 'Kaydet',
    'cancel' => 'İptal',
    'back' => 'Geri',
    'edit' => 'Düzenle',
    'delete' => 'Sil',

    // Confirmations
    'confirm_delete' => 'Bu kullanıcıyı silmek istediğinize emin misiniz?',

    // Search and Filter
    'search_users' => 'Kullanıcı Ara',
    'search_placeholder' => 'İsim, e-posta veya ID ile ara...',
    'search' => 'Ara',
    'all_roles' => 'Tüm Roller',
    'all_statuses' => 'Tüm Durumlar',

    // User Statistics
    'user_info' => 'Kullanıcı Bilgileri',
    'total_listings' => 'Toplam İlan',
    'active_listings' => 'Aktif İlanlar',
    'pending_listings' => 'Bekleyen İlanlar',
    'total_orders' => 'Toplam Sipariş',
    'total_spent' => 'Toplam Harcama',

    // Single-agency mode notices
    'single_agency_notice' => 'Bu CMS tek ofis (single-agency) modda çalışır. Yeni kullanıcı eklemek yerine var olan admin hesabını kullanın ya da ihtiyaç halinde UserSeeder içine ekleyin.',
    'create_disabled_single_agency' => 'Tek ofis modunda yeni kullanıcı eklenemez. Admin hesabı yeterlidir.',
    'agent_creation_notice' => 'Bu alan yalnızca Real Estate CMS Demo bünyesindeki danışman hesapları içindir. Sitede herkese açık kayıt veya ilan verme özelliği bulunmaz.',
    'only_agent_creation' => 'Bu ekrandan yalnızca danışman hesabı oluşturulabilir.',
    'agent_created_successfully' => 'Danışman hesabı başarıyla oluşturuldu.',
];
