<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <x-pwa-install-banner />
        <x-push-notification-card sidebar />
        <x-alert class="mb-6" />
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
