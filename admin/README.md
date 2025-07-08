# Pet Management System - Admin Panel

This directory contains the administrative interface for the Pet Management System.

## Overview

The admin panel provides system administrators with tools to manage various aspects of the application, including:

- Veterinarian registration codes
- User management
- System settings

## Access Control

Access to the admin panel is restricted to users with the following credentials:
- User type: `veterinary`
- Username: `admin`

All pages in the admin panel include authentication checks to prevent unauthorized access.

## Directory Structure

```
admin/
├── index.php              # Admin dashboard
├── manage_vet_codes.php   # Veterinarian registration code management
├── manage_users.php       # User management (placeholder)
├── system_settings.php    # System settings (placeholder)
├── sidebar.php            # Admin navigation sidebar
└── .htaccess              # Access control rules
```

## Features

### Veterinarian Code Management

The admin panel allows administrators to:
- Generate new veterinarian registration codes
- View all existing codes and their status
- Delete unused codes
- Track which veterinarians have used specific codes

### User Management (Coming Soon)

Future functionality will include:
- View all registered users
- Edit user information
- Disable/enable user accounts
- Reset user passwords

### System Settings (Coming Soon)

Future functionality will include:
- Configure application settings
- Manage email templates
- Set up notification preferences
- View system logs

## Security Considerations

- All admin pages verify user authentication and authorization
- Direct access to admin files is restricted
- Admin actions are logged for audit purposes
- Sensitive operations require confirmation
