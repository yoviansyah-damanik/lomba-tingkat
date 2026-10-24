<div wire:poll.15s="checkForChanges">
    @if ($canDecide)
        <div class="rounded-2xl border border-zinc-300 bg-gradient-to-br from-zinc-50 to-amber-50/40 p-6 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Aksi Keputusan Verifikasi Administrator</h2>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Tindakan ini akan mengunci atau membuka akses perbaikan bagi Gugus Depan, serta memicu kesiapan penerbitan kartu digital dan QR Code peserta.
                    </p>
                </div>
                @if ($isVerified)
                    <flux:badge color="emerald" icon="lock-closed" class="shrink-0">Sudah Diverifikasi</flux:badge>
                @endif
            </div>

            @if ($isVerified)
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-xs text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/30 dark:text-emerald-200">
                    <div class="flex items-center gap-2 font-bold">
                        <flux:icon name="shield-check" class="size-4" />
                        Keputusan Verifikasi Terkunci
                    </div>
                    <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                        Kontingen ini telah diverifikasi dan disetujui. Administrator tidak dapat mengubah keputusan verifikasi yang telah ditetapkan. Hubungi Superadmin jika diperlukan perubahan.
                    </p>
                </div>
            @else
                <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-emerald-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="size-4" />
                            Setujui Berkas Kontingen
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">Nyatakan berkas pendaftaran pangkalan ini valid, lengkap, dan berhak mengikuti seluruh mata lomba LT III.</p>
                        <flux:modal.trigger name="verify-modal">
                            <flux:button variant="primary" icon="check" class="mt-4 w-full bg-emerald-600 hover:bg-emerald-700">Setujui &amp; Sahkan Kontingen</flux:button>
                        </flux:modal.trigger>
                    </div>

                    <div class="rounded-xl border border-orange-200 bg-white p-4 shadow-sm dark:border-orange-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-orange-700 dark:text-orange-400">
                            <flux:icon name="arrow-path" class="size-4" />
                            Minta Perbaikan Berkas
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">Kembalikan formulir ke Gugus Depan agar data yang kurang dapat dilengkapi.</p>
                        <flux:modal.trigger name="revision-modal">
                            <flux:button variant="primary" icon="arrow-path" class="mt-4 w-full bg-orange-600 hover:bg-orange-700">Minta Perbaikan</flux:button>
                        </flux:modal.trigger>
                    </div>

                    <div class="rounded-xl border border-red-200 bg-white p-4 shadow-sm dark:border-red-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-red-700 dark:text-red-400">
                            <flux:icon name="x-circle" class="size-4" />
                            Tolak Pendaftaran
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">Pendaftaran ditolak jika pangkalan tidak memenuhi syarat kriteria kepesertaan.</p>
                        <flux:modal.trigger name="reject-modal">
                            <flux:button variant="danger" icon="x-mark" class="mt-4 w-full">Tolak Pendaftaran</flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal: setujui --}}
        <flux:modal name="verify-modal" class="w-full max-w-md" :dismissible="false">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <flux:icon name="check-circle" class="size-6" />
                    </div>
                    <div>
                        <flux:heading size="lg">Setujui Pendaftaran Kontingen?</flux:heading>
                        <flux:text class="mt-1">
                            Kontingen <strong>{{ $group->school_name }}</strong> akan dinyatakan sah. Akun peserta dan bindamping dibuat, dan keputusan ini tidak dapat diubah oleh Administrator.
                        </flux:text>
                    </div>
                </div>
                {{-- Progress: tampil selama proses berjalan (pembuatan akun peserta & bindamping) --}}
                <div wire:loading wire:target="verify" class="w-full">
                    <div wire:stream="verify-progress">
                        @include('livewire.admin.partials.verification-progress', ['percent' => 0, 'message' => 'Memulai verifikasi...'])
                    </div>
                    <p class="mt-2 text-[11px] text-zinc-500">Mohon tunggu dan jangan menutup halaman. Akun peserta dan bindamping sedang dibuat.</p>
                </div>

                <div wire:loading.remove wire:target="verify" class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="subtle">Batal</flux:button></flux:modal.close>
                    <flux:button wire:click="verify" variant="primary" icon="check" class="bg-emerald-600 hover:bg-emerald-700">Ya, Setujui</flux:button>
                </div>
            </div>
        </flux:modal>

        {{-- Modal: minta perbaikan --}}
        <flux:modal name="revision-modal" class="w-full max-w-md">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Minta Perbaikan Berkas</flux:heading>
                    <flux:text class="mt-1">Tuliskan data atau berkas yang perlu dilengkapi oleh Gugus Depan <strong>{{ $group->school_name }}</strong>.</flux:text>
                </div>
                <flux:textarea wire:model="revisionNotes" rows="4" label="Catatan Perbaikan" placeholder="Contoh: Surat tugas belum ditandatangani; foto peserta Rizky tidak jelas." />
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="subtle">Batal</flux:button></flux:modal.close>
                    <flux:button wire:click="requestRevision" variant="primary" icon="paper-airplane" class="bg-orange-600 hover:bg-orange-700">Kirim Catatan</flux:button>
                </div>
            </div>
        </flux:modal>

        {{-- Modal: tolak --}}
        <flux:modal name="reject-modal" class="w-full max-w-md">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400">
                        <flux:icon name="exclamation-triangle" class="size-6" />
                    </div>
                    <div>
                        <flux:heading size="lg">Tolak Pendaftaran Kontingen?</flux:heading>
                        <flux:text class="mt-1">Pendaftaran <strong>{{ $group->school_name }}</strong> akan ditolak. Tuliskan alasan penolakan untuk Gugus Depan.</flux:text>
                    </div>
                </div>
                <flux:textarea wire:model="rejectNotes" rows="4" label="Alasan Penolakan" placeholder="Alasan penolakan pendaftaran..." />
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="subtle">Batal</flux:button></flux:modal.close>
                    <flux:button wire:click="reject" variant="danger" icon="x-mark">Ya, Tolak</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    @if ($canCancel)
        <div class="rounded-2xl border border-red-300 bg-gradient-to-br from-red-50 to-rose-50/40 p-6 shadow-sm dark:border-red-900/50 dark:from-zinc-900 dark:to-red-950/20">
            <div class="flex items-center gap-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/60">
                    <flux:icon name="shield-exclamation" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-red-800 dark:text-red-300">Batalkan Verifikasi Kontingen</h2>
                    <p class="text-xs text-red-600/80 dark:text-red-400/80">Aksi khusus Superadmin &bull; Tidak dapat dibatalkan</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-red-200 bg-white/70 p-4 text-xs text-zinc-700 dark:border-red-900/40 dark:bg-zinc-800/60 dark:text-zinc-300">
                <p class="font-semibold text-red-700 dark:text-red-400">Dampak Pembatalan Verifikasi:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-zinc-600 dark:text-zinc-400">
                    <li>Seluruh <strong>akun digital peserta</strong> kontingen ini akan dihapus.</li>
                    <li>Seluruh <strong>akun digital bindamping</strong> kontingen ini akan dihapus.</li>
                    <li>Status kontingen dikembalikan ke <strong>Perlu Perbaikan</strong>.</li>
                    <li>Gugus Depan harus mengajukan ulang untuk diverifikasi kembali.</li>
                </ul>
            </div>

            <flux:textarea wire:model="cancelReason" rows="2" label="Alasan Pembatalan (opsional)" placeholder="Masukkan alasan pembatalan verifikasi..." class="mt-4" />

            <flux:modal.trigger name="cancel-modal">
                <flux:button variant="danger" icon="x-circle" class="mt-3 w-full">Batalkan Verifikasi &amp; Cabut Seluruh Akun</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="cancel-modal" class="w-full max-w-md">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400">
                        <flux:icon name="shield-exclamation" class="size-6" />
                    </div>
                    <div>
                        <flux:heading size="lg">Batalkan Verifikasi?</flux:heading>
                        <flux:text class="mt-1">
                            Tindakan ini akan menghapus <strong>seluruh akun peserta dan bindamping</strong> kontingen <strong>{{ $group->school_name }}</strong>. Gugus Depan harus mengajukan ulang. Tindakan tidak dapat dibatalkan.
                        </flux:text>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="subtle">Batal</flux:button></flux:modal.close>
                    <flux:button wire:click="cancelVerification" variant="danger" icon="x-circle">Ya, Batalkan &amp; Cabut Akun</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
