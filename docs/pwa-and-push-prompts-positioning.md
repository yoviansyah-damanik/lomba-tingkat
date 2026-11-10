# PWA and Web Push Floating Prompts Positioning

## Overview
Realigned and unified the floating prompts for **PWA Installation** and **Web Push Notification** into a single bottom-right stacked container (`bottom-4 right-4 sm:bottom-6 sm:right-6`). Eliminated the split layout where notifications blocked the left/center of the screen.

## Changes
1. **Unified Stack Container ([layouts/app.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/layouts/app.blade.php) & [layouts/public.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/layouts/public.blade.php))**:
   - Container: `fixed bottom-4 right-4 inset-x-4 sm:inset-x-auto sm:bottom-6 sm:right-6 z-50 flex flex-col gap-2.5 sm:gap-3 items-end max-w-sm sm:max-w-md w-[calc(100%-2rem)] sm:w-[400px] pointer-events-none print:hidden`.
   - Desktop: Cards stack vertically on the bottom-right corner, keeping the table and navigation completely unobstructed.
   - Mobile: Centered at the bottom with a 16px gutter (`inset-x-4 w-[calc(100%-2rem)]`), with compact card dimensions.
2. **Component Updates ([pwa-install-banner.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/pwa-install-banner.blade.php) & [push-notification-card.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/components/push-notification-card.blade.php))**:
   - Added `@props(['embedded' => false])`.
   - Harmonized compact card styling (`rounded-2xl sm:rounded-3xl`, `p-3.5 sm:p-5`, `size-10 sm:size-12` icons).
   - Coordinated sequencing (Desktop & Mobile): PWA install banner displays first; push notification card politely waits until the PWA prompt is resolved or dismissed (`!installBannerVisible`), ensuring only one modal prompt ever appears at a time.

