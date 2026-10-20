<x-layouts::app title="Profil Pangkalan Gugus Depan">
    <div class="mx-auto max-w-7xl space-y-6">
        <x-official-header />

        <x-heading-section
            title="Profil Pangkalan Gugus Depan"
            description="Identitas pangkalan, nomor gudep resmi, dan akun penanggung jawab kontingen."
            badge="Data Pangkalan Gudep"
            badge-icon="building-library"
            badge-color="amber"
        />

        @php($isLocked = ! $scoutGroup->isEditable())

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            {{-- Kolom Kiri: Status & Ketentuan --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-24">
                <div class="rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50/90 to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900/90">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 font-bold text-white shadow-md shadow-amber-600/20">
                            <flux:icon name="identification" class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Status Pendaftaran</h3>
                            <span class="text-[11px] font-medium text-amber-700 dark:text-amber-400">{{ $scoutGroup->registration_code }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $scoutGroup->verification_status?->badgeClasses() }}">
                            {{ $scoutGroup->verification_status?->label() ?? 'Draft (Belum Diajukan)' }}
                        </span>
                    </div>

                    @if ($isLocked)
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-100/50 p-3 text-xs text-amber-950 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200">
                            <strong class="mb-1 flex items-center gap-1.5 font-bold">
                                <flux:icon name="lock-closed" class="size-4 text-amber-700 dark:text-amber-400" />
                                Data Terkunci
                            </strong>
                            <p class="text-[11px] leading-relaxed">
                                Profil tidak dapat diubah selama pendaftaran diajukan atau setelah diverifikasi. Hubungi Sekretariat Panitia LT III bila ada data yang perlu dikoreksi.
                            </p>
                        </div>
                    @else
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-100/50 p-3 text-xs text-amber-950 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200">
                            <strong class="mb-1 flex items-center gap-1.5 font-bold">
                                <flux:icon name="information-circle" class="size-4 text-amber-700 dark:text-amber-400" />
                                Perhatian
                            </strong>
                            <p class="text-[11px] leading-relaxed">
                                Setelah pendaftaran diajukan dan diverifikasi Administrator, seluruh data profil tidak dapat diubah lagi.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Ketentuan Pangkalan</h4>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>Pangkalan resmi SMP/MTs sederajat di 6 Kwartir Ranting Kota Padangsidimpuan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>Setiap sekolah hanya mendaftarkan <strong>1 akun resmi</strong> penanggung jawab.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                            <span>Nomor WhatsApp aktif digunakan untuk konfirmasi verifikasi dan pemberitahuan pos lomba.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Kolom Kanan: Formulir --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8 lg:col-span-8">
            <form method="POST" action="{{ route('scout-group.profile.update') }}" class="space-y-6">
                @csrf

                <fieldset @disabled($isLocked) class="min-w-0 space-y-6 disabled:opacity-70">

                {{-- Section A: Identitas Pangkalan --}}
                <div>
                    <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                        <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">A</span>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            Identitas Pangkalan Gugus Depan (Sekolah)
                        </h3>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <flux:input name="school_name" :label="__('Nama Pangkalan / Sekolah')"
                                :value="old('school_name', $scoutGroup->school_name)" type="text" required
                                placeholder="Contoh: SMP Negeri 2 Padangsidimpuan" />
                            <p class="mt-1 text-[11px] text-zinc-500">Tuliskan nama resmi sekolah tingkat SMP/MTs sederajat.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Kwartir Ranting (Kwarran) <span class="text-rose-500">*</span>
                            </label>
                            <select name="kwarran" required
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">-- Pilih Kwartir Ranting (Kwarran) --</option>
                                @foreach ($kwarrans as $ranting)
                                    <option value="{{ $ranting }}" @selected(old('kwarran', $scoutGroup->kwarran_name) === $ranting)>
                                        {{ $ranting }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Alamat Lengkap Pangkalan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="address" value="{{ old('address', $streetAddress) }}" required
                                placeholder="Contoh: Jl. Sisingamangaraja No. 45, Kel. Sitamiang"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>

                        {{-- Nomor Gugus Depan (Otomatisasi Ganjil Putra & Genap Putri) --}}
                        <div class="sm:col-span-2 rounded-xl border border-zinc-200/90 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-900/40"
                            x-data="{
                                rawInput: '{{ old('group_number', $scoutGroup->male_group_number ?? $scoutGroup->group_number) }}',
                                init() {
                                    if (this.rawInput) {
                                        this.rawInput = this.formatGroupNumber(this.rawInput);
                                    }
                                },
                                formatGroupNumber(val) {
                                    if (!val) return '';
                                    let cleaned = val.replace(/[^\d-]/g, '');
                                    if (cleaned.includes('-')) {
                                        let parts = cleaned.split('-');
                                        let prefix = parts[0].slice(0, 2);
                                        let num = parts.slice(1).join('').replace(/\D/g, '').slice(0, 3);
                                        return num.length > 0 ? (prefix + '-' + num) : (prefix + '-');
                                    }
                                    let digits = cleaned.replace(/\D/g, '');
                                    if (digits.length > 2) {
                                        return digits.slice(0, 2) + '-' + digits.slice(2, 5);
                                    }
                                    return digits.slice(0, 2);
                                },
                                onInput(e) {
                                    let formatted = this.formatGroupNumber(e.target.value);
                                    this.rawInput = formatted;
                                    e.target.value = formatted;
                                },
                                handleBlur() {
                                    let clean = this.rawInput.trim();
                                    if (/^\d{1,3}$/.test(clean)) {
                                        let n = parseInt(clean, 10);
                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                        this.rawInput = '01-' + String(odd).padStart(3, '0');
                                    } else if (/^(\d{2})[-.]?(\d{1,3})$/.test(clean)) {
                                        let m = clean.match(/^(\d{2})[-.]?(\d{1,3})$/);
                                        let p = m[1];
                                        let n = parseInt(m[2], 10);
                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                        this.rawInput = p + '-' + String(odd).padStart(3, '0');
                                    }
                                },
                                get maleNum() {
                                    let clean = this.rawInput.trim();
                                    let m = clean.match(/^(\d{2})[-.]?(\d+)$/);
                                    if (m) {
                                        let p = m[1];
                                        let n = parseInt(m[2], 10);
                                        let len = Math.max(3, m[2].length);
                                        let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                        return p + '-' + String(odd).padStart(len, '0');
                                    }
                                    let digits = clean.replace(/\D/g, '');
                                    if (!digits) return '';
                                    let n = parseInt(digits, 10);
                                    let len = Math.max(3, digits.length);
                                    let odd = (n % 2 === 0) ? (n > 0 ? n - 1 : 1) : n;
                                    return '01-' + String(odd).padStart(len, '0');
                                },
                                get femaleNum() {
                                    if (!this.maleNum) return '';
                                    let parts = this.maleNum.split('-');
                                    let p = parts[0];
                                    let n = parseInt(parts[1], 10);
                                    let len = parts[1].length;
                                    return p + '-' + String(n + 1).padStart(len, '0');
                                },
                                get combined() {
                                    if (!this.maleNum || !this.femaleNum) return '';
                                    return this.maleNum + ' & ' + this.femaleNum;
                                }
                            }">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                        Nomor Gudep Putra<span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="group_number" x-model="rawInput"
                                        @input="onInput($event)" @blur="handleBlur()" required
                                        placeholder="Contoh: 01-093"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    <p class="mt-1 text-[11px] text-zinc-500">
                                        Cukup ketik nomor depan ganjil (misal: <strong>01-093</strong>). Nomor genap putri akan otomatis terisi.
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                        Nomor Gudep Putri (Otomatis)
                                    </label>
                                    <input type="text" :value="femaleNum || 'Otomatis dihitung...'"
                                        readonly
                                        class="w-full rounded-lg border border-zinc-200 bg-zinc-100/80 px-3.5 py-2 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400 cursor-not-allowed" />
                                    <p class="mt-1 text-[11px] text-zinc-500">
                                        Otomatis dihitung (nomor ganjil + 1) dan melekat pada regu putri.
                                    </p>
                                </div>
                            </div>

                            <div x-show="maleNum" x-cloak
                                class="mt-3.5 flex flex-wrap items-center gap-2 border-t border-zinc-200/80 pt-3 dark:border-zinc-800">
                                <span class="text-[11px] font-medium text-zinc-500">Hasil Format:</span>
                                <span class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                    <span class="size-1.5 rounded-full bg-blue-600"></span>
                                    Putra: <span x-text="maleNum"></span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                    <span class="size-1.5 rounded-full bg-rose-600"></span>
                                    Putri: <span x-text="femaleNum"></span>
                                </span>
                                <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-300">
                                    Format Resmi: <span x-text="combined"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section B: Akun Penanggung Jawab --}}
                <div class="pt-2">
                    <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-2 dark:border-zinc-800">
                        <span class="flex size-5 items-center justify-center rounded-md bg-amber-500/15 text-xs font-bold text-amber-700 dark:text-amber-400">B</span>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            Akun Pembina Penanggung Jawab (PIC Kontingen)
                        </h3>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <flux:input name="pic_name" :label="__('Nama Lengkap Penanggung Jawab (PIC)')"
                                :value="old('pic_name', $scoutGroup->contact_person)" type="text" required
                                placeholder="Contoh: Marwan Daulay, S.Pd" />
                            <p class="mt-1 text-[11px] text-zinc-500">Nama pembina / guru penanggung jawab pangkalan.</p>
                        </div>

                        <div>
                            <flux:input name="phone_number" :label="__('Nomor WhatsApp / HP Aktif')"
                                :value="old('phone_number', $scoutGroup->contact_phone)" type="text" required
                                placeholder="Contoh: 081234567890" />
                            <p class="mt-1 text-[11px] text-zinc-500">Digunakan Administrator untuk konfirmasi berkas verifikasi kontingen.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <flux:input name="email" :label="__('Alamat Email Resmi Kontingen')"
                                :value="old('email', auth()->user()->email)" type="email" required autocomplete="email"
                                placeholder="gudep.smpn2@gmail.com" />
                            <p class="mt-1 text-[11px] text-zinc-500">Email ini digunakan sebagai username login ke Dasbor Kontingen.</p>
                        </div>

                        <div>
                            <flux:input name="password" :label="__('Kata Sandi Baru (Opsional)')" type="password"
                                autocomplete="new-password" placeholder="Kosongkan jika tidak diubah" viewable />
                        </div>

                        <div>
                            <flux:input name="password_confirmation" :label="__('Ulangi Kata Sandi Baru')"
                                type="password" autocomplete="new-password"
                                placeholder="Ketik ulang kata sandi baru" viewable />
                        </div>
                    </div>
                </div>

                </fieldset>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <flux:button href="{{ route('scout-group.index') }}" variant="subtle">{{ $isLocked ? 'Kembali' : 'Batal' }}</flux:button>
                    @unless ($isLocked)
                        <flux:button type="submit" variant="primary" icon="check">Simpan Profil</flux:button>
                    @endunless
                </div>
            </form>
            </div>
        </div>
    </div>
</x-layouts::app>
