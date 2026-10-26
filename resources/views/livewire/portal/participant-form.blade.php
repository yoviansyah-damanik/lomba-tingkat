<div>
        <div class="flex h-full w-full flex-1 flex-col gap-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <x-official-header
                    :title="$participant ? 'Edit Biodata Peserta: ' . $participant->full_name : 'Formulir Biodata Peserta: ' . $patrol->patrol_name"
                    :subtitle="($participant ? 'Regu: ' . $patrol->patrol_name . ' • ' : 'Kategori: ') . $patrol->gender_category?->label() . ' • Gudep ' . $patrol->group_number . ' • ' . $scoutGroup->school_name"
                />
                <div class="flex items-center gap-2 self-start">
                    @if($participant && $scoutGroup->canDownloadDocuments())
                        <flux:button href="{{ route('scout-group.participants.print', $participant->id) }}" target="_blank" size="sm" variant="subtle" icon="printer">
                            Cetak Form
                        </flux:button>
                    @endif
                    <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate size="sm" variant="subtle" icon="arrow-left">
                        Kembali ke Daftar Regu
                    </flux:button>
                </div>
            </div>

            @if($errors->any())
                <div class="rounded-2xl border border-red-300 bg-red-50 p-4 text-xs text-red-900 dark:border-red-500/30 dark:bg-red-950/40 dark:text-red-200">
                    <p class="font-bold mb-1">Periksa kembali data formulir:</p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">

                {{-- 1. IDENTITAS DIRI & PAS FOTO --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 1</span>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Identitas Diri & Jabatan Regu</h2>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $patrol->gender_category?->label() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                        {{-- Pas Foto Box --}}
                        <div class="lg:col-span-1" x-data="{ photoPreview: '{{ $participant?->photo_path ? asset('storage/' . $participant?->photo_path) : '' }}' }">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-2">
                                Pas Foto 3 x 4 <span class="text-red-500">*</span>
                            </label>
                            <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="h-40 w-30 rounded-lg object-cover shadow-sm mb-2" />
                                </template>
                                <template x-if="!photoPreview">
                                    <div class="flex flex-col items-center justify-center py-6 text-zinc-400">
                                        <flux:icon name="user" class="size-12 stroke-1 mb-1 text-zinc-400" />
                                        <span class="text-xs font-medium">Ukuran 3 x 4</span>
                                        <span class="text-[10px] text-zinc-400">Seragam Pramuka Lengkap</span>
                                    </div>
                                </template>
                                <input
                                    type="file"
                                    wire:model="photo" {{ $participant?->photo_path ? '' : 'required' }}
                                    accept="image/png,image/jpeg,image/jpg"
                                    class="mt-2 block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => { photoPreview = e.target.result; };
                                            reader.readAsDataURL(file);
                                        }
                                    "
                                >
                            </div>
                            <p class="mt-1 text-[11px] text-zinc-400">Kosongkan jika tidak ingin mengubah foto.</p>
                            @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Form Fields Identitas Diri --}}
                        <div class="space-y-4 lg:col-span-3">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Nama Lengkap Peserta <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="full_name" required
                                        required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        placeholder="Nama lengkap sesuai akta kelahiran / rapor..."
                                    >
                                    @error('full_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Jabatan dalam Regu <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        wire:model="role_in_patrol"
                                        required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        @if(!$patrol->hasLeader() || $participant?->isLeader())
                                            <option value="patrol_leader">
                                                Pimpinan Regu (Pinru)
                                            </option>
                                        @endif
                                        @if(!$patrol->hasAssistantLeader() || $participant?->isAssistantLeader())
                                            <option value="assistant_patrol_leader">
                                                Wakil Pimpinan Regu (Wapinru)
                                            </option>
                                        @endif
                                        <option value="member">
                                            Anggota Regu
                                        </option>
                                    </select>
                                    @error('role_in_patrol') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        wire:model="gender"
                                        required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="male">Laki-laki (Putra)</option>
                                        <option value="female">Perempuan (Putri)</option>
                                    </select>
                                    @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Agama <span class="text-red-500">*</span>
                                </label>
                                    <select
                                        wire:model="religion" required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">Pilih Agama</option>
                                        @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $rel)
                                            <option value="{{ $rel }}">{{ $rel }}</option>
                                        @endforeach
                                    </select>
                                    @error('religion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Gol. Darah <span class="text-red-500">*</span>
                                </label>
                                    <select
                                        wire:model="blood_type" required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">-- Pilih --</option>
                                        @foreach(\App\Support\BloodType::OPTIONS as $bt => $btLabel)
                                            <option value="{{ $bt }}">{{ $btLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        NTA Pramuka <span class="text-red-500">*</span>
                                </label>
                                    <input
                                        type="text"
                                        wire:model="scout_id" required
                                        placeholder="Nomor NTA..."
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                    @error('scout_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Tempat Lahir <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="birth_place" required
                                        required
                                        placeholder="Kota kelahiran..."
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                    @error('birth_place') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Tanggal Lahir <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        wire:model="birth_date" required
                                        required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                    @error('birth_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Alamat Rumah Peserta <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="home_address" required
                                    placeholder="Jalan, No. Rumah, Lingkungan / Kelurahan..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                @error('home_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Kota / Kabupaten <span class="text-red-500">*</span>
                                </label>
                                    <input
                                        type="text"
                                        wire:model="city" required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Kode Pos <span class="text-red-500">*</span>
                                </label>
                                    <input
                                        type="text"
                                        wire:model="postal_code" required
                                        placeholder="227xx"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                        Telp. Rumah
                                </label>
                                    <input
                                        type="text"
                                        wire:model="home_phone"
                                        placeholder="0634-..."
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Handphone / WhatsApp Peserta <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="phone" required
                                    placeholder="08xxxxxxxxxx"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. DATA ORANG TUA / WALI --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 2</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Data Orang Tua / Wali</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Nama Orang Tua (Ayah / Ibu / Wali) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="parent_name" required
                                    placeholder="Nama lengkap orang tua..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Pekerjaan Orang Tua <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="parent_job" required
                                    placeholder="Contoh: PNS, Wiraswasta, Guru, TNI/Polri..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Alamat Rumah Orang Tua <span class="text-red-500">*</span>
                                </label>
                            <input
                                type="text"
                                wire:model="parent_address" required
                                placeholder="Alamat domisili orang tua..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Kota / Kabupaten <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="parent_city" required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Kode Pos <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="parent_postal_code" required
                                    placeholder="227xx"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Telp. Rumah
                                </label>
                                <input
                                    type="text"
                                    wire:model="parent_home_phone"
                                    placeholder="0634-..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Handphone / WhatsApp Orang Tua <span class="text-red-500">*</span>
                                </label>
                            <input
                                type="text"
                                wire:model="parent_phone" required
                                placeholder="08xxxxxxxxxx"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                    </div>
                </div>

                {{-- 3. PENDIDIKAN FORMAL --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 3</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Pendidikan Formal</h2>
                        <p class="text-xs text-zinc-500">Riwayat jenjang Sekolah Dasar (SD) dan Sekolah Menengah Pertama (SMP).</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-200 bg-zinc-50/70 text-zinc-500 uppercase tracking-wider dark:border-zinc-800 dark:bg-zinc-800/40">
                                <tr>
                                    <th class="py-2.5 px-3 w-20">Sekolah</th>
                                    <th class="py-2.5 px-3">Nama Sekolah <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 w-44">Kota <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 w-28">Tahun Lulus <span class="text-red-500">*</span></th>
                                    <th class="py-2.5 px-3 w-44">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($formal_education as $i => $edu)
                                    <tr>
                                        <td class="py-2.5 px-3 font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $edu['level'] ?? '' }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input
                                                type="text"
                                                wire:model="formal_education.{{ $i }}.school_name" required
                                                placeholder="Nama {{ $edu['level'] ?? '' }}..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input
                                                type="text"
                                                wire:model="formal_education.{{ $i }}.city" required
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input
                                                type="text"
                                                wire:model="formal_education.{{ $i }}.graduation_year" @if(($edu['level'] ?? '') === 'SMP') readonly @else required @endif
                                                placeholder="{{ ($edu['level'] ?? '') === 'SMP' ? 'Masih bersekolah' : 'Tahun...' }}"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input
                                                type="text"
                                                wire:model="formal_education.{{ $i }}.notes"
                                                placeholder="Ket..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. KEPRAMUKAAN: SIAGA --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 4</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Riwayat Kepramukaan: SIAGA</h2>
                        <p class="text-xs text-zinc-500">Isi riwayat kepramukaan saat golongan Siaga (jika pernah mengikuti).</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6 bg-zinc-50 p-4 rounded-xl dark:bg-zinc-800/40">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Tahun Siaga
                            </label>
                            <input
                                type="text"
                                wire:model="scout_siaga.year"
                                placeholder="Contoh: 2018 - 2021"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Nomor Gudep Siaga
                            </label>
                            <input
                                type="text"
                                wire:model="scout_siaga.group_number"
                                placeholder="Nomor Gudep..."
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Pangkalan Siaga
                            </label>
                            <input
                                type="text"
                                wire:model="scout_siaga.pangkalan"
                                placeholder="Nama SD / Madrasah..."
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {{-- Tingkatan Siaga & Kegiatan --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                Tingkatan SKU Siaga (Tahun Dicapai)
                            </h4>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach(['mula' => 'Mula', 'bantu' => 'Bantu', 'tata' => 'Tata', 'garuda' => 'Garuda'] as $key => $lbl)
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">{{ $lbl }}</label>
                                        <input
                                            type="text"
                                            wire:model="scout_siaga.levels.{{ $key }}"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                    </div>
                                @endforeach
                            </div>

                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 pt-2">
                                Giat Siaga Yang Telah Diikuti (1 - 6)
                            </h4>
                            <div class="space-y-2">
                                @for($i = 0; $i < 6; $i++)
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 text-right font-mono text-xs text-zinc-400">{{ $i + 1 }}.</span>
                                        <input
                                            type="text"
                                            wire:model="scout_siaga.activities.{{ $i }}"
                                            placeholder="Nama kegiatan siaga..."
                                            class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- TKK Siaga (20 butir) --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-3">
                                Tanda Kecakapan Khusus (TKK) Siaga
                            </h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div class="space-y-2">
                                    @for($i = 0; $i < 10; $i++)
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 text-right font-mono text-xs text-zinc-400">{{ $i + 1 }}.</span>
                                            <input
                                                type="text"
                                                wire:model="scout_siaga.tkk.{{ $i }}"
                                                placeholder="Nama TKK..."
                                                class="flex-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </div>
                                    @endfor
                                </div>
                                <div class="space-y-2">
                                    @for($i = 10; $i < 20; $i++)
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 text-right font-mono text-xs text-zinc-400">{{ $i + 1 }}.</span>
                                            <input
                                                type="text"
                                                wire:model="scout_siaga.tkk.{{ $i }}"
                                                placeholder="Nama TKK..."
                                                class="flex-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. KEPRAMUKAAN: PENGGALANG --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 5</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Riwayat Kepramukaan: PENGGALANG</h2>
                        <p class="text-xs text-zinc-500">Riwayat SKU, Kegiatan, dan TKK (Purwa, Madya, Utama) di tingkat Penggalang.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6 bg-zinc-50 p-4 rounded-xl dark:bg-zinc-800/40">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Tahun Penggalang
                            </label>
                            <input
                                type="text"
                                wire:model="scout_penggalang.year"
                                placeholder="Contoh: 2024 - Sekarang"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Nomor Gudep Penggalang
                            </label>
                            <input
                                type="text"
                            
                                value="{{ $patrol->group_number }}" readonly
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                                Pangkalan Penggalang
                            </label>
                            <input
                                type="text"
                            
                                value="{{ $scoutGroup->school_name }}" readonly
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                    </div>

                    {{-- Tingkatan SKU Penggalang & Kegiatan --}}
                    <div class="mb-6 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            Tingkatan SKU Penggalang (Tahun Dicapai)
                        </h4>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach(['ramu' => 'Ramu', 'rakit' => 'Rakit', 'terap' => 'Terap', 'garuda' => 'Garuda'] as $key => $lbl)
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">{{ $lbl }}</label>
                                    <input
                                        type="text"
                                        wire:model="scout_penggalang.levels.{{ $key }}"
                                        placeholder="Tahun..."
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                </div>
                            @endforeach
                        </div>

                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 pt-2">
                            Giat Penggalang Yang Telah Diikuti (1 - 7)
                        </h4>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @for($i = 0; $i < 7; $i++)
                                <div class="flex items-center gap-2">
                                    <span class="w-5 text-right font-mono text-xs text-zinc-400">{{ $i + 1 }}.</span>
                                    <input
                                        type="text"
                                        wire:model="scout_penggalang.activities.{{ $i }}"
                                        placeholder="Nama kegiatan penggalang..."
                                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- TKK Penggalang (Purwa, Madya, Utama - masing-masing 10) --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-3">
                            Tanda Kecakapan Khusus (TKK) Penggalang
                        </h4>
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            {{-- PURWA --}}
                            <div class="rounded-xl border border-zinc-200 p-3 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/30">
                                <span class="block text-xs font-bold uppercase text-amber-700 dark:text-amber-400 mb-2 text-center">
                                    TKK PURWA (Tingkat 1)
                                </span>
                                <div class="space-y-1.5">
                                    @for($i = 0; $i < 10; $i++)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-5 text-right font-mono text-[11px] text-zinc-400">{{ $i + 1 }}.</span>
                                            <input
                                                type="text"
                                                wire:model="scout_penggalang.tkk_purwa.{{ $i }}"
                                                placeholder="TKK Purwa..."
                                                class="flex-1 rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            {{-- MADYA --}}
                            <div class="rounded-xl border border-zinc-200 p-3 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/30">
                                <span class="block text-xs font-bold uppercase text-blue-700 dark:text-blue-400 mb-2 text-center">
                                    TKK MADYA (Tingkat 2)
                                </span>
                                <div class="space-y-1.5">
                                    @for($i = 0; $i < 10; $i++)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-5 text-right font-mono text-[11px] text-zinc-400">{{ $i + 1 }}.</span>
                                            <input
                                                type="text"
                                                wire:model="scout_penggalang.tkk_madya.{{ $i }}"
                                                placeholder="TKK Madya..."
                                                class="flex-1 rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            {{-- UTAMA --}}
                            <div class="rounded-xl border border-zinc-200 p-3 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/30">
                                <span class="block text-xs font-bold uppercase text-emerald-700 dark:text-emerald-400 mb-2 text-center">
                                    TKK UTAMA (Tingkat 3)
                                </span>
                                <div class="space-y-1.5">
                                    @for($i = 0; $i < 10; $i++)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-5 text-right font-mono text-[11px] text-zinc-400">{{ $i + 1 }}.</span>
                                            <input
                                                type="text"
                                                wire:model="scout_penggalang.tkk_utama.{{ $i }}"
                                                placeholder="TKK Utama..."
                                                class="flex-1 rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. UNGGAH BERKAS PERSYARATAN PESERTA --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-4 mb-6 dark:border-zinc-800">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 6</span>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Berkas Persyaratan Peserta</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Unggah atau perbarui berkas dokumen persyaratan peserta dalam format PDF, maksimal 4MB per berkas.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                            7 Berkas Persyaratan
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        {{-- 1. Scan KTA --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    1. Scan Kartu Tanda Anggota (KTA) Pramuka
                                </label>
                                @if($participant?->doc_kta_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_kta_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Kartu Tanda Anggota Pramuka resmi peserta.</p>
                            <input
                                type="file"
                                wire:model="doc_kta"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_kta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 2. Scan BPJS --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    2. Scan Kartu BPJS / Asuransi Kesehatan
                                </label>
                                @if($participant?->doc_bpjs_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_bpjs_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Kartu BPJS Kesehatan aktif atau asuransi sejenis.</p>
                            <input
                                type="file"
                                wire:model="doc_bpjs"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_bpjs') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 3. Scan Surat Keterangan Sehat --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    3. Scan Surat Keterangan Sehat
                                </label>
                                @if($participant?->doc_health_certificate_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_health_certificate_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Surat keterangan berbadan sehat dari dokter/puskesmas.</p>
                            <input
                                type="file"
                                wire:model="doc_health_certificate"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_health_certificate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 4. Scan Akta Kelahiran --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    4. Scan Akta Kelahiran
                                </label>
                                @if($participant?->doc_birth_certificate_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_birth_certificate_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Akta kelahiran resmi untuk verifikasi batas usia penggalang.</p>
                            <input
                                type="file"
                                wire:model="doc_birth_certificate"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_birth_certificate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 5. Scan Kartu Keluarga --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    5. Scan Kartu Keluarga (KK)
                                </label>
                                @if($participant?->doc_family_card_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_family_card_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Kartu Keluarga orang tua / wali peserta.</p>
                            <input
                                type="file"
                                wire:model="doc_family_card"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_family_card') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 6. Scan SKU --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    6. Scan Syarat Kecakapan Umum (SKU)
                                </label>
                                @if($participant?->doc_sku_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_sku_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Buku SKU / surat pengesahan pelantikan SKU tingkat Ramu/Rakit/Terap.</p>
                            <input
                                type="file"
                                wire:model="doc_sku"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_sku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- 7. Scan SKK --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30 md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    7. Scan Syarat Kecakapan Khusus (SKK)
                                </label>
                                @if($participant?->doc_skk_path)
                                    <x-file-viewer-trigger :path="$participant?->doc_skk_path" :title="'Berkas Peserta — '.$participant?->full_name" label="Lihat Berkas" icon="document-check" />
                                @else
                                    <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 mb-2">Surat keterangan / sertifikat Tanda Kecakapan Khusus (TKK) yang dimiliki peserta.</p>
                            <input
                                type="file"
                                wire:model="doc_skk"
                                accept=".pdf,application/pdf"
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300"
                            >
                            @error('doc_skk') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:button href="{{ route('scout-group.patrols') }}" wire:navigate variant="subtle">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $participant ? 'Perbarui' : 'Simpan' }} Biodata Peserta
                    </flux:button>
                </div>
            </form>
        </div>
        <x-file-viewer />
</div>
