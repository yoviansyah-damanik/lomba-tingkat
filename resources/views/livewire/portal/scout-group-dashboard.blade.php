@php
    use App\Enums\PatrolRoleEnum;
    use App\Enums\RegistrationStatusEnum;

    $isEditable = $scoutGroup->isEditable();

    $genders = [
        'boys' => [
            'label' => 'Putra',
            'number' => $scoutGroup->male_group_number,
            'patrols' => $boysPatrols,
            'chaperone' => $scoutGroup->chaperones->firstWhere('gender', 'male'),
            'chaperoneGender' => 'male',
            'accent' => 'text-blue-700 dark:text-blue-400',
            'chip' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
            'bar' => '[&::-moz-progress-bar]:bg-blue-600 [&::-webkit-progress-value]:bg-blue-600',
            'ring' => 'border-blue-200/70 dark:border-blue-900/40',
        ],
        'girls' => [
            'label' => 'Putri',
            'number' => $scoutGroup->female_group_number,
            'patrols' => $girlsPatrols,
            'chaperone' => $scoutGroup->chaperones->firstWhere('gender', 'female'),
            'chaperoneGender' => 'female',
            'accent' => 'text-rose-700 dark:text-rose-400',
            'chip' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
            'bar' => '[&::-moz-progress-bar]:bg-rose-600 [&::-webkit-progress-value]:bg-rose-600',
            'ring' => 'border-rose-200/70 dark:border-rose-900/40',
        ],
    ];
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

    {{-- Batas waktu pengisian --}}
    @if($activeEvent && $activeEvent->registration_end_at && $isEditable)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-300/80 bg-amber-50/80 p-4 text-xs text-amber-950 shadow-xs dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
            <div class="flex items-center gap-3">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white">
                    <flux:icon name="clock" class="size-4" />
                </div>
                <div>
                    <span class="block font-bold">Batas waktu pengisian regu, peserta, dan bindamping</span>
                    <span class="text-[11px] text-amber-900/80 dark:text-amber-300/80">
                        {{ $activeEvent->registration_end_at->translatedFormat('d F Y, H:i') }} WIB &bull;
                        sisa <strong>{{ (int) ceil(now()->diffInDays($activeEvent->registration_end_at)) }} hari</strong>. Ajukan seluruh berkas sebelum batas waktu ditutup.
                    </span>
                </div>
            </div>
            <flux:button href="{{ route('scout-group.review') }}" wire:navigate size="sm" variant="primary" icon="clipboard-document-check">
                Periksa Kelengkapan
            </flux:button>
        </div>
    @endif

    {{-- Identitas pangkalan --}}
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900" aria-labelledby="gudep-title">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-0.5 font-mono text-xs font-bold text-amber-800 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-300">
                        {{ $scoutGroup->registration_code }}
                    </span>
                    <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $scoutGroup->verification_status?->badgeClasses() }}">
                        {{ $scoutGroup->verification_status?->label() }}
                    </span>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Portal Gugus Depan</p>
                    <h1 id="gudep-title" class="text-xl font-extrabold tracking-tight text-zinc-900 sm:text-2xl dark:text-zinc-100">
                        {{ $scoutGroup->school_name }}
                    </h1>
                </div>

                <dl class="grid grid-cols-1 gap-x-6 gap-y-2 text-xs sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <flux:icon name="hashtag" class="size-4 shrink-0 text-zinc-400" />
                        <dt class="sr-only">Nomor Gudep</dt>
                        <dd class="flex flex-wrap gap-1.5">
                            <span class="rounded px-1.5 py-0.5 font-mono font-bold {{ $genders['boys']['chip'] }}">Putra {{ $scoutGroup->male_group_number ?: '-' }}</span>
                            <span class="rounded px-1.5 py-0.5 font-mono font-bold {{ $genders['girls']['chip'] }}">Putri {{ $scoutGroup->female_group_number ?: '-' }}</span>
                        </dd>
                    </div>
                    <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                        <flux:icon name="user" class="size-4 shrink-0 text-zinc-400" />
                        <dt class="sr-only">Penanggung jawab</dt>
                        <dd><span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->contact_person }}</span> &bull; {{ $scoutGroup->contact_phone }}</dd>
                    </div>
                    <div class="flex items-start gap-2 text-zinc-600 sm:col-span-2 dark:text-zinc-400">
                        <flux:icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                        <dt class="sr-only">Alamat</dt>
                        <dd>{{ $scoutGroup->address ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                @if($scoutGroup->canDownloadDocuments())
                    <flux:button href="{{ route('scout-group.registration-certificate', ['download' => 1]) }}" variant="primary" icon="document-text"
                        data-loading="Mencetak Bukti Registrasi (PDF)...">
                        Bukti Registrasi (PDF)
                    </flux:button>
                @endif
                @if($isEditable)
                    <flux:button variant="subtle" icon="pencil-square" wire:click="openProfileModal">Edit Profil</flux:button>
                    <flux:button href="{{ route('scout-group.review') }}" wire:navigate variant="primary" icon="arrow-right">Periksa &amp; Ajukan</flux:button>
                @elseif(! $scoutGroup->canDownloadDocuments())
                    <flux:badge icon="lock-closed" color="zinc">Data terkunci (dalam verifikasi)</flux:badge>
                @endif
            </div>
        </div>

        @if($scoutGroup->verification_status === RegistrationStatusEnum::NeedsRevision && $scoutGroup->revision_notes)
            <div class="mt-5 rounded-xl border border-orange-300 bg-orange-50/80 p-4 text-xs text-orange-900 dark:border-orange-500/30 dark:bg-orange-950/40 dark:text-orange-200">
                <strong class="flex items-center gap-1.5 font-bold">
                    <flux:icon name="exclamation-triangle" class="size-4 text-orange-600" />
                    Catatan perbaikan dari Administrator
                </strong>
                <p class="mt-1.5 pl-5 leading-relaxed">{{ $scoutGroup->revision_notes }}</p>
            </div>
        @endif
    </section>

    {{-- Ringkasan angka --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Regu Terdaftar" :value="$scoutGroup->patrols->count()" unit="regu" description="Putra {{ $boysPatrols->count() }} &bull; Putri {{ $girlsPatrols->count() }}" icon="user-group" color="emerald" />
        <x-stat-card label="Peserta" :value="$participantsTotal" :unit="'/ '.$participantsTarget" description="Wajib 8 peserta per regu" icon="users" color="indigo" />
        <x-stat-card label="Bindamping" :value="$scoutGroup->chaperones->count()" :unit="'/ '.max(1, count($requiredChaperoneGenders))" description="Sesuai regu putra/putri" icon="user-circle" color="blue" />
        <x-stat-card label="Berkas Administrasi" :value="collect([$scoutGroup->mandate_letter_path, $scoutGroup->payment_proof_path])->filter()->count()" unit="/ 2" description="Surat tugas &amp; bukti bayar" icon="document-check" color="amber" />
    </div>

    {{-- Regu putra & putri --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($genders as $category => $cat)
            <section wire:key="dashboard-patrols-{{ $category }}" class="flex flex-col rounded-2xl border {{ $cat['ring'] }} bg-white p-5 shadow-sm dark:bg-zinc-900" aria-label="Regu {{ $cat['label'] }}">
                <div class="flex items-center justify-between gap-2 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-bold uppercase tracking-wider {{ $cat['accent'] }}">Regu {{ $cat['label'] }}</h2>
                        <span class="rounded px-1.5 py-0.5 font-mono text-[11px] font-bold {{ $cat['chip'] }}">Gudep {{ $cat['number'] ?: '-' }}</span>
                    </div>
                    <flux:badge size="sm" :color="$cat['patrols']->isEmpty() ? 'zinc' : 'emerald'">
                        {{ $cat['patrols']->isEmpty() ? 'Belum ada' : $cat['patrols']->count().' regu' }}
                    </flux:badge>
                </div>

                <div class="mt-4 flex-1 space-y-3">
                    @forelse($cat['patrols'] as $patrol)
                        @php
                            $count = $patrol->participants->count();
                            $hasLeader = $patrol->participants->contains(fn ($p) => $p->role_in_patrol === PatrolRoleEnum::PatrolLeader);
                            $hasAssistant = $patrol->participants->contains(fn ($p) => $p->role_in_patrol === PatrolRoleEnum::AssistantPatrolLeader);
                            $complete = $scoutGroup->rosterProblemsFor($patrol) === [];
                        @endphp
                        <article wire:key="dashboard-patrol-{{ $patrol->id }}" class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-bold text-zinc-900 dark:text-zinc-100">Regu {{ $patrol->patrol_name }}</h3>
                                    <p class="text-[11px] text-zinc-500">
                                        Bindamping: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $patrol->chaperone?->full_name ?? 'belum ditetapkan' }}</span>
                                    </p>
                                </div>
                                <flux:badge size="sm" :color="$complete ? 'emerald' : 'amber'" :icon="$complete ? 'check-circle' : 'exclamation-circle'">
                                    {{ $complete ? 'Lengkap' : 'Belum lengkap' }}
                                </flux:badge>
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <progress value="{{ $count }}" max="{{ \App\Models\Patrol::REQUIRED_MEMBERS }}"
                                    class="h-1.5 flex-1 overflow-hidden rounded-full [&::-webkit-progress-bar]:bg-zinc-100 dark:[&::-webkit-progress-bar]:bg-zinc-800 {{ $cat['bar'] }}"></progress>
                                <span class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400">{{ $count }}/{{ \App\Models\Patrol::REQUIRED_MEMBERS }} peserta</span>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold">
                                <span class="rounded-md px-1.5 py-0.5 {{ $hasLeader ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' }}">Pinru {{ $hasLeader ? '✓' : '—' }}</span>
                                <span class="rounded-md px-1.5 py-0.5 {{ $hasAssistant ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' }}">Wapinru {{ $hasAssistant ? '✓' : '—' }}</span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate variant="subtle" size="xs" icon-trailing="arrow-right">Kelola Peserta</flux:button>
                                @if($scoutGroup->canDownloadDocuments())
                                    <flux:button href="{{ route('scout-group.patrols.form-01', $patrol->id) }}" target="_blank" variant="subtle" size="xs" icon="printer"
                                        data-loading="Mencetak FORM 01 Regu {{ $patrol->patrol_name }} (PDF)...">FORM 01</flux:button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="flex h-full min-h-32 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                            <flux:icon name="user-group" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-xs text-zinc-500">Regu {{ strtolower($cat['label']) }} belum didaftarkan.</p>
                        </div>
                    @endforelse
                </div>

                @if($isEditable)
                    <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate variant="primary" size="sm" icon="plus" class="mt-4 w-full">
                        Daftarkan Regu {{ $cat['label'] }}
                    </flux:button>
                @endif
            </section>
        @endforeach
    </div>

    {{-- Pembina pendamping: satu slot per gender, mengikuti regu --}}
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900" aria-labelledby="chaperone-title">
        <div class="flex items-center justify-between gap-2 border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <h2 id="chaperone-title" class="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pembina Pendamping</h2>
            <span class="text-[11px] text-zinc-500">Pendaftaran bindamping ada di menu Bindamping</span>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($genders as $cat)
                @php
                    $chaperone = $cat['chaperone'];
                    $canRegister = in_array($cat['chaperoneGender'], $requiredChaperoneGenders, true);
                @endphp
                <div wire:key="dashboard-chaperone-{{ $cat['chaperoneGender'] }}" class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider {{ $cat['accent'] }}">Bindamping {{ $cat['label'] }}</p>
                        @if($chaperone)
                            <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $chaperone->full_name }}</p>
                            <p class="font-mono text-[11px] text-zinc-500">{{ $chaperone->phone_number }}</p>
                        @elseif($canRegister)
                            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Belum didaftarkan</p>
                            <p class="text-[11px] text-zinc-500">Wajib karena Regu {{ $cat['label'] }} sudah ada.</p>
                        @else
                            <p class="text-sm font-semibold text-zinc-400">Menunggu Regu {{ $cat['label'] }}</p>
                            <p class="text-[11px] text-zinc-500">Daftarkan regu terlebih dahulu.</p>
                        @endif
                    </div>

                    @if($chaperone)
                        <flux:button href="{{ route('scout-group.chaperones.show', $chaperone->id) }}" wire:navigate size="xs" variant="subtle" icon="eye">Detail</flux:button>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Modal Edit Profil Pangkalan --}}
    <flux:modal wire:model="showProfileModal" class="max-w-xl">
        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <flux:heading size="lg">Edit Profil Pangkalan Gugus Depan</flux:heading>
                <flux:subheading>Pastikan nama pangkalan, nomor gudep, dan kontak PIC terisi akurat.</flux:subheading>
            </div>

            <flux:input wire:model="school_name" label="Nama Sekolah / Pangkalan *" placeholder="SMP Negeri 1 Padangsidimpuan" />
            <flux:input wire:model="group_number" label="Nomor Gudep *" placeholder="01-093 (otomatis menjadi 01-093 & 01-094)" />
            <flux:textarea wire:model="address" label="Alamat Pangkalan *" placeholder="Alamat lengkap pangkalan sekolah..." rows="2" />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <flux:input wire:model="contact_person" label="Nama Kontak PIC *" placeholder="Nama Pembina Gudep" />
                <flux:input wire:model="contact_phone" label="Nomor HP / WhatsApp PIC *" placeholder="08123456789" />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showProfileModal', false)">Batal</flux:button>
                <flux:button type="submit" variant="primary">Simpan Profil</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
