# Superadmin Registration Notifications

## Overview
Scout group registration notifications are now broadcast to both Superadmin and Administrator accounts (both in-app notifications and Web Push).

## Implementation Details
- **File**: `app/Jobs/SendScoutGroupRegisteredPush.php`
- **Roles Targeted**: `RoleEnum::Superadmin` and `RoleEnum::Admin`
- **Channels**:
  - In-app dasbor notification bell via `InAppNotificationService`
  - Browser/PWA Web Push Notification via `WebPushService`
- **Tests**: `tests/Feature/Auth/ScoutGroupRegistrationTest.php` verified delivery to both roles.

