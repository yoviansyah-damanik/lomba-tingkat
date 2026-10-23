# Configurable Registration Fee and Payment Information

## Overview
Added configurable registration fee per patrol and payment account details displayed dynamically across public and portal registration pages.

## Key Changes
1. **Database & Model**:
   - Migration `add_registration_fee_and_payment_info_to_events_table`: adds `registration_fee_per_patrol`, `payment_bank_name`, `payment_account_number`, `payment_account_holder`, `payment_instructions` to `events` table.
   - [Event](file:///d:/WebApps/lomba-tingkat/app/Models/Event.php): added fields to `$fillable` and helper getters for formatting fee and payment details with fallbacks to `config/registration.php`.
2. **Admin Configuration**:
   - [SystemSettingsManagement](file:///d:/WebApps/lomba-tingkat/app/Livewire/Admin/SystemSettingsManagement.php): inputs and validation for registration fee and bank account details.
   - [system-settings-management.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/admin/system-settings-management.blade.php): responsive card for managing registration fee and bank payment details using Tailwind CSS.
3. **Public Landing Page**:
   - [welcome.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/welcome.blade.php): dynamic display of registration fee per patrol, account transfer details, and payment flow guidelines.
4. **Portal Review & Upload Proof**:
   - [ReviewRegistration](file:///d:/WebApps/lomba-tingkat/app/Livewire/Portal/ReviewRegistration.php) & [review-registration.blade.php](file:///d:/WebApps/lomba-tingkat/resources/views/livewire/portal/review-registration.blade.php): dynamic total payment calculation (patrol count &times; fee), target account details card, and proof upload handling per contingent/squad.
