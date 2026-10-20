<x-layouts::app title="Profil Pangkalan Gugus Depan">
    <div class="mx-auto max-w-4xl space-y-6">
        <x-official-header />

        <x-heading-section
            title="Profil Pangkalan Gugus Depan"
            description="Kelola nama pangkalan, nomor gudep resmi, dan penanggung jawab kontingen."
            badge="Data Pangkalan Gudep"
            badge-icon="building-library"
            badge-color="amber"
        />

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <form method="POST" action="{{ route('scout-group.profile.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                        Nama Pangkalan / Sekolah <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="school_name"
                        value="{{ old('school_name', $scoutGroup->school_name) }}"
                        required
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        placeholder="Contoh: SMP Negeri 1 Padangsidimpuan"
                    >
                    @error('school_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-xl border border-zinc-200/90 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-900/40" x-data="{
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
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                                Nomor Gudep Depan (Ganjil - Putra) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="group_number"
                                x-model="rawInput"
                                @input="onInput($event)"
                                @blur="handleBlur()"
                                required
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                placeholder="Contoh: 01-093"
                            >
                            <p class="mt-1 text-[11px] text-zinc-500">Cukup ketik nomor depan ganjil. Nomor genap putri akan terhitung otomatis.</p>
                            @error('group_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                                Nomor Gudep Putri (Genap - Otomatis)
                            </label>
                            <input
                                type="text"
                                :value="femaleNum || 'Otomatis dihitung...'"
                                readonly
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-100 px-4 py-2.5 font-medium text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400 cursor-not-allowed"
                            >
                            <p class="mt-1 text-[11px] text-zinc-500">Otomatis dihitung (nomor ganjil + 1) untuk regu putri.</p>
                        </div>
                    </div>

                    {{-- Live Preview Tag --}}
                    <div x-show="maleNum" x-cloak class="mt-3.5 flex flex-wrap items-center gap-2 border-t border-zinc-200/80 pt-3 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-500">Hasil Format:</span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                            <span class="size-1.5 rounded-full bg-blue-600"></span>
                            Putra: <span x-text="maleNum"></span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                            <span class="size-1.5 rounded-full bg-rose-600"></span>
                            Putri: <span x-text="femaleNum"></span>
                        </span>
                        <span class="text-zinc-500">
                            Format Resmi: <strong class="text-zinc-800 dark:text-zinc-200" x-text="combined"></strong>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                        Alamat Pangkalan / Sekolah <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="address"
                        rows="3"
                        required
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        placeholder="Alamat lengkap pangkalan di Kota Padangsidimpuan..."
                    >{{ old('address', $scoutGroup->address) }}</textarea>
                    @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Nama Penanggung Jawab (PIC) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="contact_person"
                            value="{{ old('contact_person', $scoutGroup->contact_person) }}"
                            required
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                        @error('contact_person') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Nomor WhatsApp / Ponsel <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="contact_phone"
                            value="{{ old('contact_phone', $scoutGroup->contact_phone) }}"
                            required
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            placeholder="08123456789"
                        >
                        @error('contact_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <a href="{{ route('scout-group.index') }}" class="rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300">
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-700"
                    >
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
