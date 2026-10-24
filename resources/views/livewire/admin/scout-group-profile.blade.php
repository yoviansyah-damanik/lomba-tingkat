<div class="space-y-6">
    <x-heading-section
        :title="$g->school_name"
        :description="'Informasi lengkap pangkalan gugus depan • Gudep '.($g->group_number ?: '-').' • '.$g->kwarran_name"
        :badge="$g->registration_code"
        badge-icon="building-library"
        badge-color="amber"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.gudep-directory') }}" wire:navigate variant="subtle" icon="arrow-left">
                Pangkalan Gugusdepan
            </flux:button>
            @if(auth()->user()->isAdmin())
                <flux:button href="{{ route('admin.scout-groups.show', $g->id) }}" wire:navigate variant="primary" icon="shield-check">
                    Halaman Verifikasi
                </flux:button>
            @endif
        </x-slot:actions>
    </x-heading-section>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-stat-card label="Regu" :value="number_format($g->patrols->count())" description="Putra & putri" color="blue" icon="user-group" />
        <x-stat-card label="Peserta" :value="number_format($g->patrols->sum(fn ($p) => $p->participants->count()))" description="Penggalang" color="emerald" icon="academic-cap" />
        <x-stat-card label="Bindamping" :value="number_format($g->chaperones->count())" description="Pembina pendamping" color="rose" icon="user-circle" />
        <x-stat-card label="Status" :value="$g->verification_status?->label() ?? '-'" :description="$g->verified_at ? 'Sejak '.$g->verified_at->translatedFormat('d M Y') : null" color="amber" icon="check-badge" />
    </div>

    @include('livewire.admin.scout-group-profile.identity')
    @include('livewire.admin.scout-group-profile.patrols')
    @include('livewire.admin.scout-group-profile.chaperones')
    @include('livewire.admin.scout-group-profile.documents')

    <x-file-viewer />
</div>
