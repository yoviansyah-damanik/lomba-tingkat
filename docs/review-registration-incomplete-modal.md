# Review Registration Incomplete Requirements Modal

## Overview
Provides an interactive modal alert on `/portal/scout-group/review` when users click the submission button while requirements are incomplete.

## Features
- **Incomplete Requirements Detection (`missingRequirements`):**
  - **Base Profile:** Checks if school name or group number are missing.
  - **Patrols:** Checks for at least one registered patrol.
  - **Patrol Members:** Warns if any registered patrol has zero participants.
  - **Chaperones:** Checks for at least one registered chaperone.
  - **Administrative Documents:** Checks if Mandate Letter or Payment Proof are missing.
- **Interactive Incomplete Modal (`<flux:modal wire:model="showIncompleteModal">`):**
  - Displays count of missing items.
  - Lists each missing requirement with category, title, description, and direct action button (`<flux:button size="xs">`).
  - Displays informative note if participant document scans (7 files) are pending.
- **Submission Confirmation Modal (`<flux:modal wire:model="showSubmitModal">`):**
  - Shown only when all requirements are fully satisfied.
  - Summarizes contingent profile, patrol & participant counts, and chaperone data.
  - Warns that data will be locked upon submission.
