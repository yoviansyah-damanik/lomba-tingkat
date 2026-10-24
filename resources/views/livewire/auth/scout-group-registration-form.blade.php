<div class="py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('livewire.auth.partials.registration-header')

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
            <div class="lg:col-span-4">
                @include('livewire.auth.partials.registration-sidebar')
            </div>

            <div class="lg:col-span-8">
                @if (! $isRegistrationOpen)
                    @include('livewire.auth.partials.registration-closed')
                @elseif ($invitation)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                                <strong class="font-bold flex items-center gap-1.5 text-sm mb-1">
                                    <flux:icon name="exclamation-circle" class="size-4 text-rose-600" />
                                    Mohon periksa kembali isian formulir pendaftaran:
                                </strong>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form wire:submit="register" class="space-y-6">
                            {{-- Section A: Identitas Pangkalan (dari kode undangan, terkunci) --}}
                            <div>
                                <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">A</span>
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Identitas Pangkalan Gugus Depan (Sekolah)</h3>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <flux:input :value="$invitation->school_name" label="Nama Pangkalan / Sekolah" type="text" readonly disabled />
                                    </div>

                                    <flux:input :value="$invitation->kwarran_name" label="Kwartir Ranting (Kwarran)" type="text" readonly disabled />

                                    <flux:input wire:model="address" label="Alamat Lengkap Pangkalan" type="text" required autofocus
                                        placeholder="Contoh: Jl. Sisingamangaraja No. 45, Kel. Sitamiang" />

                                    <flux:input :value="$groupNumbers['male']" label="Nomor Gudep Putra" type="text" readonly disabled />
                                    <flux:input :value="$groupNumbers['female']" label="Nomor Gudep Putri" type="text" readonly disabled />

                                    <p class="sm:col-span-2 text-[11px] text-zinc-500">
                                        Identitas pangkalan ditetapkan panitia melalui kode undangan <span class="font-mono font-semibold">{{ $invitation->formattedCode() }}</span> dan tidak dapat diubah.
                                    </p>
                                </div>
                            </div>

                            {{-- Section B: Akun Penanggung Jawab (PIC) --}}
                            <div class="pt-2">
                                <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">B</span>
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Akun Pembina Penanggung Jawab (PIC Kontingen)</h3>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <flux:input wire:model="pic_name" label="Nama Lengkap Penanggung Jawab (PIC)" type="text" required
                                            placeholder="Contoh: Marwan Daulay, S.Pd" />
                                        <p class="mt-1 text-[11px] text-zinc-500">Nama pembina / guru penanggung jawab pangkalan.</p>
                                    </div>

                                    <div>
                                        <flux:input wire:model="phone_number" label="Nomor WhatsApp / HP Aktif" type="text" required
                                            placeholder="Contoh: 081234567890" />
                                        <p class="mt-1 text-[11px] text-zinc-500">Digunakan Administrator untuk konfirmasi berkas verifikasi kontingen.</p>
                                    </div>

                                    <div class="sm:col-span-2 rounded-xl border border-amber-300/80 bg-gradient-to-br from-amber-50/90 to-amber-100/50 p-4 dark:border-amber-500/30 dark:from-amber-950/40 dark:to-zinc-900/60 shadow-xs">
                                        <div class="flex items-start gap-3">
                                            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white shadow-xs">
                                                <flux:icon name="sparkles" class="size-4" />
                                            </div>
                                            <div class="text-xs">
                                                <strong class="block font-bold text-amber-950 dark:text-amber-200">Kredensial Akun Dibuat Otomatis oleh Sistem</strong>
                                                <p class="mt-0.5 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                                    Anda tidak perlu membuat username dan kata sandi secara manual. Sistem akan menerbitkan <strong>ID Gudep resmi unik</strong>, <strong>username</strong>, dan <strong>kata sandi aman</strong> segera setelah pendaftaran dikirim, lalu Anda dapat langsung mengubah kata sandi.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section C: Pakta Pernyataan --}}
                            <div class="rounded-xl border border-amber-200/80 bg-amber-50/50 p-4 dark:border-amber-500/20 dark:bg-amber-950/30">
                                <flux:checkbox wire:model="terms"
                                    label="Saya menyatakan bahwa data identitas pangkalan dan penanggung jawab di atas adalah benar dan sah mewakili Gugus Depan pangkalan sekolah, serta bersedia mematuhi seluruh Petunjuk Pelaksanaan dan Petunjuk Teknis Lomba Tingkat III Kwarcab Padangsidimpuan." />
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 pt-2">
                                <flux:button href="{{ route('home') }}" wire:navigate variant="subtle" icon="arrow-left">
                                    Batal &amp; Kembali ke Beranda
                                </flux:button>

                                <flux:button type="submit" variant="primary" icon="arrow-right" class="w-full sm:w-auto" data-test="register-scout-group-button">
                                    Daftarkan Pangkalan &amp; Masuk Dasbor
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
