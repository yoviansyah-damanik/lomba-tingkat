# Reusable Stat Card Component (`<x-stat-card>`)

## Overview
`<x-stat-card>` is a unified Blade component for KPI metrics, counter summaries, interactive status filters, and action cards.

Component path: `resources/views/components/stat-card.blade.php`

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | string | required | Card header title / KPI metric label |
| `value` | string\|number\|null | null | Main metric number or text value |
| `unit` | string\|null | null | Unit label following the value (e.g. `Regu`, `Sesi`) |
| `description` | string\|null | null | Secondary subtitle or helper text |
| `icon` | string\|null | null | Flux icon name displayed in top right badge |
| `color` | string | `'neutral'` | Color theme (`neutral`, `amber`, `blue`, `emerald`, `indigo`, `purple`, `rose`, `orange`) |
| `href` | string\|null | null | Renders card as an `<a>` with `wire:navigate` |
| `actionText` | string\|null | null | Action link text with trailing arrow |
| `asButton` | bool | `false` | Renders as `<button type="button">` for interactive filter tabs |
| `active` | bool | `false` | Active state highlight ring and background (useful with `asButton`) |

## Usage Examples

### 1. Basic Stat Metric
```blade
<x-stat-card
    label="Peserta Penggalang"
    :value="$totalParticipants"
    unit="Orang"
    description="Peserta terdaftar"
    color="indigo"
    icon="identification"
/>
```

### 2. Clickable Navigation Card
```blade
<x-stat-card
    label="Sesi Jadwal"
    :value="$totalSchedules"
    unit="Sesi Terjadwal"
    action-text="Lihat Jadwal & Arena"
    href="{{ route('admin.schedules.index') }}"
    color="amber"
    icon="calendar"
/>
```

### 3. Interactive Filter Tab
```blade
<x-stat-card
    as-button
    wire:click="$set('statusFilter', 'all')"
    :active="$statusFilter === 'all'"
    label="Semua Pangkalan"
    :value="$counts['all']"
    color="neutral"
/>
```

### 4. Custom Slot (e.g. Badge or Complex Content)
```blade
<x-stat-card
    label="Status Berkas"
    action-text="Buka Portal"
    href="{{ route('scout-group.index') }}"
    color="amber"
    icon="identification"
>
    <div class="mt-2">
        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-bold bg-amber-50 text-amber-700">
            Terverifikasi
        </span>
    </div>
</x-stat-card>
```

## Adopted Views
- `resources/views/livewire/portal/attendance-recap.blade.php`
- `resources/views/livewire/portal/attendance-dashboard.blade.php`
- `resources/views/livewire/admin/schedules-management.blade.php`
- `resources/views/livewire/admin/competitions-management.blade.php`
- `resources/views/livewire/admin/officials-management.blade.php`
- `resources/views/livewire/admin/verification-management.blade.php`
- `resources/views/livewire/admin/participants-directory.blade.php`
- `resources/views/livewire/admin/chaperones-directory.blade.php`
- `resources/views/livewire/dashboard.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/admin/schedules/index.blade.php`
- `resources/views/admin/competitions/index.blade.php`
