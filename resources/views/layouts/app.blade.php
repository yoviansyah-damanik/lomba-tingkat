<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <x-pwa-install-banner />
        <x-push-notification-card sidebar />
        <x-alert />
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
