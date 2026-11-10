<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    @if ($record && $record['is_eligible'])
        <div
            class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Header Status Verifikasi --}}
            <div class="bg-linear-to-r from-emerald-600 via-emerald-500 to-teal-600 p-6 sm:p-8 text-white text-center">
                <div
                    class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xs text-white mb-3 shadow-inner">
                    <flux:icon name="check-badge" class="size-10" />
                </div>
                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">E-Sertifikat Sah &amp; Terverifikasi</h1>
                <p class="text-xs sm:text-sm text-emerald-100 mt-1">
                    Dokumen ini tercatat secara resmi dalam basis data penyelenggaraan Lomba Tingkat III Kwarcab Kota
                    Padangsidimpuan.
                </p>
            </div>

            {{-- Body Dokumen --}}
            <div class="p-6 sm:p-8 space-y-6">
                <div class="rounded-2xl bg-zinc-50 p-5 dark:bg-zinc-800/50 space-y-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Nama Penerima</span>
                        <h2 class="text-xl font-black text-zinc-900 dark:text-zinc-100">{{ $record['full_name'] }}</h2>
                    </div>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs border-t border-zinc-200/60 pt-3 dark:border-zinc-700/60">
                        <div>
                            <span class="text-zinc-400">Nomor Tanda Anggota (NTA):</span>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-0.5">{{ $record['scout_id'] }}
                            </p>
                        </div>
                        <div>
                            <span class="text-zinc-400">Peran / Predikat:</span>
                            <p class="font-bold text-amber-700 dark:text-amber-400 mt-0.5">{{ $record['role_label'] }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-zinc-400">Pangkalan / Gugusdepan:</span>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-0.5">
                                {{ $record['institution'] }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-zinc-400">Nomor Registrasi Sertifikat:</span>
                            <p class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">
                                {{ $record['certificate_number'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-zinc-500 dark:text-zinc-400 space-y-1">
                    <p><span class="font-semibold text-zinc-700 dark:text-zinc-300">Kegiatan:</span> Lomba Tingkat Tiga
                        (LT-III) Regu Pramuka Penggalang</p>
                    <p><span class="font-semibold text-zinc-700 dark:text-zinc-300">Penyelenggara:</span> Kwartir Cabang
                        Gerakan Pramuka Kota Padangsidimpuan</p>
                    <p><span class="font-semibold text-zinc-700 dark:text-zinc-300">Status Penerbitan:</span>
                        <span class="font-bold {{ $isPublished ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $isPublished ? 'Diterbitkan Resmi (Aktif)' : 'Draf (Menunggu Publikasi)' }}
                        </span>
                    </p>
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button href="{{ route('certificates.index') }}" wire:navigate icon="arrow-left"
                        variant="outline">
                        Laman Sertifikat
                    </flux:button>

                    @if ($isPublished && $downloadUrl)
                        <flux:button href="{{ $downloadUrl }}" icon="arrow-down-tray" variant="primary">
                            Unduh Berkas E-Sertifikat
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div
            class="rounded-3xl border border-zinc-200 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-12 space-y-4">
            <div
                class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                <flux:icon name="exclamation-circle" class="size-8" />
            </div>
            <div class="max-w-md mx-auto space-y-2">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Dokumen Tidak Ditemukan</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Kode identitas <span class="font-mono font-bold text-rose-600">{{ $id }}</span> tidak
                    tercatat sebagai penerima sertifikat yang terverifikasi dalam sistem.
                </p>
            </div>
            <div class="pt-2">
                <flux:button href="{{ route('certificates.index') }}" wire:navigate icon="magnifying-glass"
                    variant="outline">
                    Cari Sertifikat Lain
                </flux:button>
            </div>
        </div>
    @endif
</div>
