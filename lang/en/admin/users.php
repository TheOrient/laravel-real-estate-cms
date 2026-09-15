<?php

return [
    // Titles
    'manage_users' => 'User Management',
    'manage_team' => 'Team & Consultant Management',
    'all_users' => 'All Users',
    'create_user' => 'Create New User',
    'create_agent' => 'Add Consultant',
    'edit_user' => 'Edit User',
    'user_details' => 'User Details',

    // Table columns
    'id' => 'ID',
    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'name' => 'Full Name',
    'email' => 'Email',
    'role' => 'Role',
    'phone' => 'Phone',
    'enable_whatsapp' => 'Show WhatsApp',
    'yes' => 'Yes',
    'no' => 'No',
    'status' => 'Status',
    'created_at' => 'Registration Date',
    'actions' => 'Actions',

    // Form labels
    'user_role' => 'User Role',
    'password' => 'Password',
    'password_confirmation' => 'Confirm Password',
    'is_active' => 'Active',
    'email_verified' => 'Email Verified',
    'leave_blank' => "Leave blank if you don't want to change",

    // Roles
    'admin' => 'Admin',
    'user' => 'User',
    'agent' => 'Consultant',

    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'verified' => 'Verified',
    'unverified' => 'Unverified',

    // Alerts
    'created_successfully' => 'User created successfully.',
    'updated_successfully' => 'User updated successfully.',
    'deleted_successfully' => 'User deleted successfully.',
    'moved_to_trash' => 'User moved to trash.',
    'deleted_permanently' => 'User deleted permanently.',
    'restored_successfully' => 'User restored successfully.',
    'active_users' => 'Active Users',
    'trashed_users' => 'Trashed Users',
    'cannot_delete_self' => 'You cannot delete yourself.',
    'cannot_edit_super_admin' => 'Super admin user cannot be edited.',
    'cannot_delete_super_admin' => 'Super admin user cannot be deleted.',
    'cannot_change_super_admin_password' => 'Super admin password can only be changed by themselves.',
    'cannot_change_super_admin_role' => 'Super admin role cannot be changed.',
    'super_admin_protected' => 'This user is protected by the system.',

    // Buttons
    'create' => 'New User',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'back' => 'Back',
    'edit' => 'Edit',
    'delete' => 'Delete',

    // Confirmations
    'confirm_delete' => 'Are you sure you want to delete this user?',

    // Search and Filter
    'search_users' => 'Search Users',
    'search_placeholder' => 'Search by name, email or ID...',
    'search' => 'Search',
    'all_roles' => 'All Roles',
    'all_statuses' => 'All Statuses',

    // User Statistics
    'user_info' => 'User Information',
    'total_listings' => 'Total Listings',
    'active_listings' => 'Active Listings',
    'pending_listings' => 'Pending Listings',
    'total_orders' => 'Total Orders',
    'total_spent' => 'Total Spent',

    // Single-agency mode notices
    'single_agency_notice' => 'This CMS runs in single-agency mode. Instead of adding new users, use the existing admin account or extend UserSeeder if you really need additional accounts.',
    'create_disabled_single_agency' => 'New users cannot be added in single-agency mode. The admin account is sufficient.',
    'agent_creation_notice' => 'This area is exclusively for Real Estate CMS Demo consultant accounts. Public registration and listing submission are not available.',
    'only_agent_creation' => 'Only consultant accounts can be created from this screen.',
    'agent_created_successfully' => 'Consultant account created successfully.',
];
