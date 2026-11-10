<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{-- Floating System Prompts (PWA Install & Web Push) --}}
        <div class="fixed bottom-4 right-4 inset-x-4 sm:inset-x-auto sm:bottom-6 sm:right-6 z-50 flex flex-col gap-2.5 sm:gap-3 items-end max-w-sm sm:max-w-md w-[calc(100%-2rem)] sm:w-[400px] pointer-events-none print:hidden">
            <x-pwa-install-banner embedded />
            <x-push-notification-card embedded />
        </div>
        <x-alert />
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
