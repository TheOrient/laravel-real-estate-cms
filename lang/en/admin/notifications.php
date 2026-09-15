<?php

return [
    // Page titles
    'title' => 'Notifications',
    'create_title' => 'Send New Notification',

    // Actions
    'send_notification' => 'Send New Notification',
    'mark_all_read' => 'Mark All as Read',
    'mark_selected_read' => 'Mark Selected as Read',
    'mark_selected_unread' => 'Mark Selected as Unread',
    'delete_selected' => 'Delete Selected',
    'send' => 'Send Notification',
    'cancel' => 'Cancel',

    // Form fields
    'target' => 'Target',
    'target_admin' => 'Admin (Myself)',
    'target_user' => 'Specific User',
    'target_all_users' => 'All Users',
    'select_user' => 'Select User',
    'select_target' => 'Select target...',
    'select_user_placeholder' => 'Select user...',
    'title_field' => 'Title',
    'message_field' => 'Message',
    'important' => 'Important Notification',
    'custom_icon' => 'Custom Icon (Optional)',
    'action_links' => 'Action Links (Optional)',
    'link_title' => 'Link title',
    'link_url' => 'URL',
    'link_type' => 'Type',
    'link_primary' => 'Primary',
    'link_secondary' => 'Secondary',
    'link_danger' => 'Danger',
    'add_link' => 'Add Link',

    // Table headers
    'status' => 'Status',
    'type' => 'Type',
    'sender' => 'Sender',
    'date' => 'Date',
    'actions' => 'Actions',

    // Statuses
    'read' => 'Read',
    'unread' => 'Unread',
    'important_label' => 'Important',
    'new_label' => 'New',

    // Stats
    'total_notifications' => 'Total Notifications',
    'unread_notifications' => 'Unread',

    // Messages
    'no_notifications' => 'No notifications yet',
    'no_notifications_desc' => 'New notifications will appear here when they arrive.',
    'notification_sent' => 'Notification sent successfully.',
    'notifications_marked_read' => 'Notifications marked as read.',
    'notification_deleted' => 'Notification deleted.',
    'please_select_notifications' => 'Please select notifications to perform action.',
    'confirm_delete' => 'Are you sure you want to delete this notification?',
    'confirm_delete_selected' => 'Are you sure you want to delete the selected notifications?',
    'notification_immutable' => 'Notifications cannot be changed after being sent.',

    // Validation messages
    'validation_title_required' => 'Title is required.',
    'validation_message_required' => 'Message is required.',
    'validation_target_required' => 'You must select a target.',
    'validation_user_required_for_user_target' => 'You must specify a user when user target is selected.',
    'validation_action_links_incomplete' => 'Both title and URL must be filled for action links.',

    // Help texts
    'important_help' => 'Important notifications are displayed with emphasis.',
    'icon_help' => 'Font Awesome class. If left empty, an icon will be automatically selected based on notification type.',
    'icon_examples' => 'Examples: fas fa-info-circle, fas fa-exclamation-triangle, fas fa-check-circle',
    'action_links_help' => 'You can add links so users can quickly take action from the notification.',

    // Notification types
    'type_listing_approved' => 'Listing Approved',
    'type_listing_rejected' => 'Listing Rejected',
    'type_listing_expired' => 'Listing Expired',
    'type_new_listing' => 'New Listing',
    'type_new_report' => 'New Report',
    'type_new_message' => 'New Message',
    'type_custom' => 'Custom',

    // Breadcrumbs
    'breadcrumb_dashboard' => 'Dashboard',
    'breadcrumb_notifications' => 'Notifications',
    'breadcrumb_new_notification' => 'New Notification',
];
