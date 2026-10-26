<div class="py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 border-b border-zinc-200/80 pb-6 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300">
                    <span class="size-1.5 rounded-full bg-amber-600"></span>
                    Pendaftaran Mandiri Aparatur
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                    Pendaftaran Panitia & Dewan Juri
                </h1>
                <p class="mt-1 max-w-2xl text-xs text-zinc-600 dark:text-zinc-400 sm:text-sm">
                    Bergabunglah sebagai aparatur {{ $event?->title ?? 'LT III' }}. Akun aktif setelah disetujui Administrator.
                </p>
            </div>

            @if ($event?->official_registration_end_at)
                <div class="flex items-center gap-3 self-start rounded-2xl border p-3.5 shadow-xs sm:self-auto {{ $isOpen ? 'border-amber-200 bg-amber-50/70 dark:border-amber-900/40 dark:bg-amber-950/30' : 'border-rose-200 bg-rose-50/70 dark:border-rose-900/40 dark:bg-rose-950/30' }}">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl text-white shadow-xs {{ $isOpen ? 'bg-amber-600' : 'bg-rose-600' }}">
                        <flux:icon name="calendar" class="size-4.5" />
                    </div>
                    <div class="text-xs">
                        <span class="block text-[10px] font-semibold uppercase tracking-wider {{ $isOpen ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                            Batas Waktu Pendaftaran
                        </span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $event->official_registration_end_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            {{-- Panel informasi --}}
            <div class="space-y-6 lg:sticky lg:top-24 lg:col-span-4">
                <div class="rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50/90 to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900/90">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-md shadow-amber-600/20">
                            <flux:icon name="user-plus" class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Alur Pendaftaran Aparatur</h3>
                            <span class="text-[11px] font-medium text-amber-700 dark:text-amber-400">LT III Kwarcab Padangsidimpuan</span>
                        </div>
                    </div>

                    <ol class="mt-5 space-y-3.5 text-xs">
                        <li class="flex items-start gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-[11px] font-bold text-white shadow-xs">1</span>
                            <div>
                                <strong class="block font-semibold text-zinc-900 dark:text-zinc-100">Isi Formulir & Unggah Pas Foto</strong>
                                <span class="text-zinc-500">Lengkapi identitas, NTA, jabatan, dan pilih peran.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">2</span>
                            <div>
                                <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Simpan Username & Kata Sandi</strong>
                                <span class="text-zinc-500">Diterbitkan sistem; wajib diperbarui saat login pertama.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">3</span>
                            <div>
                                <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Persetujuan Administrator</strong>
                                <span class="text-zinc-500">Data diperiksa lalu akun diaktifkan. Anda baru dapat masuk setelah itu.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-[11px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">4</span>
                            <div>
                                <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Kartu Digital & Penugasan</strong>
                                <span class="text-zinc-500">Kartu QR dan jadwal tugas tersedia di dasbor setelah akun aktif.</span>
                            </div>
                        </li>
                    </ol>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Ketentuan</h4>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>NTA dan jabatan <strong>wajib diisi</strong> sesuai kartu anggota dan surat tugas.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>Pas foto 3 x 4 berseragam, latar polos, format JPG/PNG maks. 2 MB.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>Pendaftaran tidak memerlukan email; login memakai ID aparatur dan kata sandi dari sistem.</span>
                        </li>
                    </ul>

                    <div class="mt-4 border-t border-zinc-100 pt-3 text-xs dark:border-zinc-800">
                        <span class="text-zinc-500">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-amber-700 hover:underline dark:text-amber-400">Masuk &rarr;</a>
                    </div>
                </div>
            </div>

            {{-- Konten utama --}}
            <div class="lg:col-span-8">
                @if ($registeredId)
                    <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm dark:border-emerald-900/50 dark:bg-zinc-900">
                        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-8 text-center text-white">
                            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-white/20">
                                <flux:icon name="check-circle" class="size-8" />
                            </div>
                            <h2 class="mt-3 text-xl font-extrabold">Pendaftaran Berhasil Dikirim</h2>
                            <p class="mx-auto mt-1 max-w-md text-xs text-emerald-50">Terima kasih telah bergabung. Data Anda akan segera diperiksa Administrator.</p>
                        </div>
                        <div class="space-y-5 p-6 text-center sm:p-8" x-data="{
                            copied: null,
                            username: @js($registeredId),
                            password: @js($registeredPassword),
                            loginUrl: @js(route('login')),
                            copy(text, key) {
                                const done = () => { this.copied = key; setTimeout(() => this.copied = null, 2000); };
                                if (navigator.clipboard && window.isSecureContext) {
                                    navigator.clipboard.writeText(text).then(done);
                                    return;
                                }
                                // Cadangan untuk koneksi non-HTTPS / peramban lama
                                const field = document.createElement('textarea');
                                field.value = text;
                                field.className = 'fixed opacity-0';
                                document.body.appendChild(field);
                                field.select();
                                document.execCommand('copy');
                                field.remove();
                                done();
                            },
                            all() { return `Akun Aparatur LT III\nUsername: ${this.username}\nKata sandi awal: ${this.password}\nMasuk: ${this.loginUrl}`; },
                            download() {
                                const url = URL.createObjectURL(new Blob([this.all() + '\n'], { type: 'text/plain' }));
                                const link = document.createElement('a');
                                link.href = url;
                                link.download = `akun-aparatur-${this.username}.txt`;
                                link.click();
                                URL.revokeObjectURL(url);
                                this.copied = 'download';
                                setTimeout(() => this.copied = null, 2000);
                            },
                        }">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">ID Aparatur (Username Login)</span>
                                    <div class="mt-2 select-all rounded-xl border border-dashed border-emerald-400 bg-emerald-50 px-4 py-3 font-mono text-xl font-extrabold tracking-widest text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                                        {{ $registeredId }}
                                    </div>
                                    <flux:button type="button" size="xs" variant="subtle" class="mt-2" x-on:click="copy(username, 'username')" x-bind:icon="copied === 'username' ? 'check' : 'clipboard-document'">
                                        <span x-text="copied === 'username' ? 'Tersalin' : 'Salin Username'">Salin Username</span>
                                    </flux:button>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Kata Sandi Awal</span>
                                    <div class="mt-2 select-all rounded-xl border border-dashed border-amber-400 bg-amber-50 px-4 py-3 font-mono text-xl font-extrabold tracking-widest text-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                        {{ $registeredPassword }}
                                    </div>
                                    <flux:button type="button" size="xs" variant="subtle" class="mt-2" x-on:click="copy(password, 'password')" x-bind:icon="copied === 'password' ? 'check' : 'clipboard-document'">
                                        <span x-text="copied === 'password' ? 'Tersalin' : 'Salin Kata Sandi'">Salin Kata Sandi</span>
                                    </flux:button>
                                </div>
                            </div>
                            <div class="mx-auto max-w-lg rounded-xl border border-amber-300 bg-amber-50/90 p-3 text-left text-xs leading-relaxed text-amber-900 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-200">
                                <strong class="font-bold">Simpan username dan kata sandi ini sekarang.</strong> Kata sandi hanya ditampilkan satu kali. Akun berstatus <strong>menunggu persetujuan</strong> dan baru dapat masuk setelah diaktifkan Administrator. Pada login pertama Anda <strong>wajib memperbarui kata sandi</strong>.
                            </div>
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <flux:button type="button" size="sm" icon="clipboard-document-list" x-on:click="copy(all(), 'all')">
                                    <span x-text="copied === 'all' ? 'Tersalin!' : 'Salin Semua'">Salin Semua</span>
                                </flux:button>
                                <flux:button type="button" size="sm" icon="arrow-down-tray" x-on:click="download()">
                                    <span x-text="copied === 'download' ? 'Tersimpan!' : 'Simpan sebagai .txt'">Simpan sebagai .txt</span>
                                </flux:button>
                            </div>
                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <flux:button href="{{ route('login') }}" wire:navigate variant="primary" icon="arrow-right-end-on-rectangle">Ke Halaman Masuk</flux:button>
                                <flux:button href="{{ route('home') }}" wire:navigate icon="home">Kembali ke Beranda</flux:button>
                            </div>
                        </div>
                    </div>
                @elseif (! $isOpen)
                    <div class="rounded-2xl border border-rose-200 bg-white p-8 text-center shadow-sm dark:border-rose-900/50 dark:bg-zinc-900">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                            <flux:icon name="lock-closed" class="size-7" />
                        </div>
                        <h2 class="mt-4 text-xl font-bold text-zinc-900 dark:text-zinc-100">Pendaftaran Aparatur Sedang Ditutup</h2>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            @if ($event?->official_registration_start_at && now()->lt($event->official_registration_start_at))
                                Pendaftaran dibuka mulai {{ $event->official_registration_start_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB.
                            @else
                                Tautan pendaftaran aparatur tidak menerima pendaftaran saat ini. Hubungi panitia Kwarcab untuk informasi lebih lanjut.
                            @endif
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <flux:button href="{{ route('login') }}" wire:navigate variant="primary" icon="arrow-right-end-on-rectangle">Masuk</flux:button>
                            <flux:button href="{{ route('home') }}" wire:navigate icon="home">Kembali ke Beranda</flux:button>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                                <strong class="mb-1 flex items-center gap-1.5 text-sm font-bold">
                                    <flux:icon name="exclamation-circle" class="size-4 text-rose-600" />
                                    Mohon periksa kembali isian formulir:
                                </strong>
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form wire:submit="register" class="space-y-8">
                            {{-- A. Identitas & foto --}}
                            <div>
                                <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">A</span>
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Identitas Aparatur</h3>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-[auto_1fr]">
                                    {{-- Pas foto 3x4 --}}
                                    <div class="mx-auto sm:mx-0">
                                        <label for="official-photo" class="group relative flex h-44 w-[132px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/60 text-center transition hover:border-amber-500 hover:bg-amber-50 dark:border-amber-500/30 dark:bg-amber-950/20 {{ $errors->has('photo') ? 'border-rose-400 bg-rose-50/60' : '' }}">
                                            @if ($photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                                <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau pas foto" class="size-full object-cover">
                                                <span class="absolute inset-x-0 bottom-0 bg-zinc-900/70 py-1 text-[10px] font-semibold text-white opacity-0 transition group-hover:opacity-100">Ganti Foto</span>
                                            @else
                                                <flux:icon name="camera" class="size-8 text-amber-500" />
                                                <span class="mt-2 text-[11px] font-bold text-amber-800 dark:text-amber-300">Pas Foto 3 x 4</span>
                                                <span class="mt-0.5 px-2 text-[10px] text-zinc-500">Klik untuk memilih</span>
                                            @endif
                                        </label>
                                        <input id="official-photo" type="file" wire:model="photo" accept="image/png,image/jpeg" class="sr-only">
                                        <p class="mt-2 w-[132px] text-center text-[10px] leading-snug text-zinc-500">JPG/PNG, maks. 2 MB <span class="text-rose-500">*</span></p>
                                        <div wire:loading wire:target="photo" class="mt-1 w-[132px] text-center text-[10px] font-semibold text-amber-700">Mengunggah foto...</div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <flux:input wire:model="name" label="Nama Lengkap (beserta gelar)" required autocomplete="name" placeholder="Contoh: Drs. Ahmad Fauzi Lubis" />
                                        </div>
                                        <flux:input wire:model="scout_id" label="NTA (Nomor Tanda Anggota)" required placeholder="Contoh: 02.03.04.0123" />
                                        <flux:input wire:model="phone_number" label="Nomor WhatsApp / Telepon" required autocomplete="tel" placeholder="Contoh: 081234567890" />
                                        <flux:input wire:model="institution" label="Instansi / Kwartir" required placeholder="Contoh: DKC Padangsidimpuan / Kwarcab Padangsidimpuan" />
                                        <flux:input wire:model="position" label="Jabatan" required placeholder="Contoh: Pembina Gudep / Andalan Kwarran" />
                                    </div>
                                </div>
                            </div>

                            {{-- B. Peran --}}
                            <div>
                                <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">B</span>
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Mendaftar Sebagai <span class="font-normal text-zinc-500">(boleh keduanya)</span></h3>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['value' => 'committee', 'icon' => 'clipboard-document-check', 'title' => 'Panitia', 'desc' => 'Pemantau operasional kegiatan, presensi, dan verifikasi di lapangan.'],
                                        ['value' => 'judge', 'icon' => 'scale', 'title' => 'Dewan Juri', 'desc' => 'Penilai mata lomba pada sesi yang ditugaskan panitia.'],
                                    ] as $option)
                                        <label wire:key="role-{{ $option['value'] }}" class="relative block cursor-pointer">
                                            <input type="checkbox" wire:model.live="roles" value="{{ $option['value'] }}" class="peer sr-only">
                                            <div class="flex h-full items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:peer-checked:bg-amber-950/30">
                                                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ in_array($option['value'], $roles, true) ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' }}">
                                                    <flux:icon :name="$option['icon']" class="size-5" />
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <span class="block text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $option['title'] }}</span>
                                                    <span class="mt-0.5 block text-[11px] leading-relaxed text-zinc-500">{{ $option['desc'] }}</span>
                                                </div>
                                                @if (in_array($option['value'], $roles, true))
                                                    <flux:icon name="check-circle" variant="solid" class="size-5 shrink-0 text-amber-600" />
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @if (in_array('committee', $roles, true))
                                    <div class="mt-4">
                                        <flux:input wire:model="committee_position" label="Posisi Kepanitiaan" required placeholder="Contoh: Ketua Panitia / Sekretaris / Koordinator Lapangan" />
                                        <p class="mt-1 text-[11px] text-zinc-500">Ditampilkan pada ID Card{{ in_array('judge', $roles, true) ? ' bersama "Dewan Juri"' : '' }}.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- C. Akun --}}
                            <div>
                                <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">C</span>
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Akun Login</h3>
                                </div>

                                <div class="mt-4 rounded-xl border border-amber-300/80 bg-gradient-to-br from-amber-50/90 to-amber-100/50 p-4 shadow-xs dark:border-amber-500/30 dark:from-amber-950/40 dark:to-zinc-900/60">
                                    <div class="flex items-start gap-3">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white shadow-xs">
                                            <flux:icon name="sparkles" class="size-4" />
                                        </div>
                                        <div class="text-xs">
                                            <strong class="block font-bold text-amber-950 dark:text-amber-200">Username & Kata Sandi Diterbitkan Otomatis</strong>
                                            <p class="mt-0.5 leading-relaxed text-zinc-600 dark:text-zinc-400">
                                                Sistem menerbitkan <strong>ID aparatur</strong> sebagai username dan kata sandi awal setelah formulir dikirim. Pada login pertama, Anda <strong>wajib memperbarui kata sandi</strong>. Anda tidak perlu membuat username atau email.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse items-center justify-between gap-4 border-t border-zinc-200/80 pt-5 dark:border-zinc-800 sm:flex-row">
                                <flux:button href="{{ route('login') }}" wire:navigate variant="subtle" icon="arrow-left">Kembali ke Halaman Masuk</flux:button>
                                <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full sm:w-auto" data-test="register-official-button" wire:loading.attr="disabled" wire:target="register,photo">
                                    Kirim Pendaftaran
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
