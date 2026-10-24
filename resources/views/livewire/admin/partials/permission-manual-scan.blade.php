{{-- Input manual bila kamera tidak tersedia: tautan verifikasi atau token QR --}}
<form wire:submit="scanManual" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
    <flux:input wire:model="manualInput" label="Input Manual" placeholder="Tempel tautan verifikasi atau token QR" icon="link" />
    <flux:button type="submit" variant="subtle" icon="magnifying-glass" class="mt-3 w-full">Proses</flux:button>
</form>
