<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-heading-section
        title="Kode Undangan Registrasi"
        description="Daftarkan gugus depan yang diizinkan mendaftar. Sistem membuat satu kode undangan sekali pakai untuk tiap gugus depan; bagikan kode kepada pembina yang bersangkutan."
        badge="Keamanan Registrasi"
        badge-icon="key"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.registration-invitations.print-slips') }}" target="_blank" icon="printer" variant="subtle">
                Cetak Surat Undangan
            </flux:button>
            <flux:button href="{{ route('admin.registration-invitations.print-recap') }}" target="_blank" icon="document-text" variant="subtle">
                Cetak Rekap Undangan
            </flux:button>
            <flux:button wire:click="$dispatch('open-invitation-form')" variant="primary" icon="plus">
                Tambah Gugus Depan
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    <livewire:admin.registration-invitations-list lazy />
</div>
