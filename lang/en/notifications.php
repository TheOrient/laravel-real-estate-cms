<?php

return [
    // Page titles
    'title' => 'My Notifications',
    'my_notifications' => 'My Notifications',

    // Actions
    'mark_all_read' => 'Mark All as Read',
    'mark_read' => 'Mark as Read',
    'mark_unread' => 'Mark as Unread',
    'delete' => 'Delete',
    'view_all' => 'View All Notifications',

    // States
    'read' => 'Read',
    'unread' => 'Unread',
    'important' => 'Important',
    'new' => 'New',

    // Messages
    'no_notifications' => 'You have no notifications yet',
    'no_notifications_desc' => 'New notifications will appear here when they arrive.',
    'no_new_notifications' => 'You have no new notifications',
    'unread_count' => 'unread notification',

    // Actions messages
    'marked_as_read' => 'Notification marked as read.',
    'marked_as_unread' => 'Notification marked as unread.',
    'all_marked_as_read' => 'All notifications marked as read.',
    'notification_deleted' => 'Notification deleted.',
    'notifications_deleted' => 'notifications deleted.',

    // Confirmation messages
    'confirm_delete' => 'Are you sure you want to delete this notification?',
    'confirm_bulk_delete' => 'Are you sure you want to delete the selected notifications?',

    // Error messages
    'error_occurred' => 'An error occurred.',
    'failed_to_load' => 'Failed to load notifications.',
    'failed_to_mark_read' => 'Failed to mark notification as read.',
    'failed_to_delete' => 'Failed to delete notification.',

    // Time
    'time_ago' => [
        'just_now' => 'Just now',
        'minutes_ago' => 'minutes ago',
        'hours_ago' => 'hours ago',
        'days_ago' => 'days ago',
        'weeks_ago' => 'weeks ago',
        'months_ago' => 'months ago',
        'years_ago' => 'years ago',
    ],

    // Notification content templates
    'listing_approved_title' => 'Your Listing Has Been Approved!',
    'listing_approved_message' => 'Your listing titled ":title" has been approved and published.',

    'listing_rejected_title' => 'Listing Not Approved',
    'listing_rejected_message' => 'Your listing titled ":title" was not approved.',
    'listing_rejected_with_reason' => 'Your listing titled ":title" was not approved. Reason: :reason',

    'listing_submitted_title' => 'Your Listing Has Been Submitted for Approval',
    'listing_submitted_message' => 'Your listing titled ":title" has been created successfully and sent for admin approval. You will receive a notification when it is approved.',

    'listing_resubmitted_title' => 'Your Listing Changes Have Been Submitted for Approval',
    'listing_resubmitted_message' => 'The changes you made to your listing titled ":title" have been saved and resubmitted for admin approval. The listing will remain published in its old form until approved.',

    'listing_resubmitted_admin_title' => 'Listing Edited and Awaiting Re-approval',
    'listing_resubmitted_admin_message' => 'User :user has edited their listing titled ":title". The listing is awaiting your approval again.',

    'listing_expired_title' => 'Listing Expired and Deactivated',
    'listing_expired_message' => 'Your listing titled ":title" has expired after 30 days of publication and has been removed because you have no package rights remaining. You can purchase a package to republish it.',

    'listing_renewed_title' => 'Your Listing Has Been Automatically Renewed',
    'listing_renewed_message' => 'Your listing titled ":title" had expired and has been automatically extended for another 30 days. New expiration date: :expires_at',

    'new_message_title' => 'New Message',
    'new_message_content' => ':sender sent you a message about your listing ":listing".',

    // Action buttons
    'view_listing' => 'View Listing',
    'edit_listing' => 'Edit Listing',
    'my_listings' => 'My Listings',
    'view_message' => 'View Message',
    'all_messages' => 'All Messages',
    'renew_listing' => 'Renew Listing',
    'buy_package' => 'Buy Package',
    'my_package' => 'My Package',

    // Page description
    'page_description' => 'You can view all your notifications here',

    // Navigation
    'notifications_menu' => 'My Notifications',
];
