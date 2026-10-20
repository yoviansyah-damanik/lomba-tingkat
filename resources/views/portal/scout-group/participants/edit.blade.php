<x-layouts::app :title="'Edit Biodata Peserta — ' . $participant->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <x-official-header
                :title="'Edit Biodata Peserta: ' . $participant->full_name"
                :subtitle="'Regu: ' . $patrol->patrol_name . ' • ' . $patrol->gender_category?->label() . ' • Gudep ' . $patrol->group_number . ' • ' . $scoutGroup->school_name"
            />
            <div class="flex items-center gap-2 self-start">
                <a href="{{ route('scout-group.participants.print', $participant->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-amber-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400">
                    <flux:icon name="printer" class="size-3.5" />
                    Cetak Form
                </a>
                <a href="{{ route('scout-group.patrols') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    &larr; Kembali
                </a>
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

        <form method="POST" action="{{ route('scout-group.participants.update', $participant->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

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
                    <div class="lg:col-span-1" x-data="{ photoPreview: '{{ $participant->photo_path ? asset('storage/' . $participant->photo_path) : '' }}' }">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-2">
                            Pas Foto 3 x 4
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
                                name="photo"
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
                                    name="full_name"
                                    value="{{ old('full_name', $participant->full_name) }}"
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
                                    name="role_in_patrol"
                                    required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    @php $currentRole = old('role_in_patrol', $participant->role_in_patrol?->value ?? $participant->role_in_patrol); @endphp
                                    @if(!$patrol->hasLeader() || $participant->isLeader())
                                        <option value="patrol_leader" {{ $currentRole === 'patrol_leader' ? 'selected' : '' }}>
                                            Pimpinan Regu (Pinru)
                                        </option>
                                    @endif
                                    @if(!$patrol->hasAssistantLeader() || $participant->isAssistantLeader())
                                        <option value="assistant_patrol_leader" {{ $currentRole === 'assistant_patrol_leader' ? 'selected' : '' }}>
                                            Wakil Pimpinan Regu (Wapinru)
                                        </option>
                                    @endif
                                    <option value="member" {{ $currentRole === 'member' ? 'selected' : '' }}>
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
                                    name="gender"
                                    required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    <option value="male" {{ old('gender', $participant->gender) === 'male' ? 'selected' : '' }}>Laki-laki (Putra)</option>
                                    <option value="female" {{ old('gender', $participant->gender) === 'female' ? 'selected' : '' }}>Perempuan (Putri)</option>
                                </select>
                                @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Agama
                                </label>
                                <select
                                    name="religion"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    <option value="">Pilih Agama</option>
                                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $rel)
                                        <option value="{{ $rel }}" {{ old('religion', $participant->religion) === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                                @error('religion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Gol. Darah
                                </label>
                                <select
                                    name="blood_type"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    <option value="">-</option>
                                    @foreach(['A', 'B', 'AB', 'O'] as $bt)
                                        <option value="{{ $bt }}" {{ old('blood_type', $participant->blood_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                    @endforeach
                                </select>
                                @error('blood_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    NTA Pramuka
                                </label>
                                <input
                                    type="text"
                                    name="scout_id"
                                    value="{{ old('scout_id', $participant->scout_id) }}"
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
                                    name="birth_place"
                                    value="{{ old('birth_place', $participant->birth_place) }}"
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
                                    name="birth_date"
                                    value="{{ old('birth_date', $participant->birth_date?->format('Y-m-d')) }}"
                                    required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                @error('birth_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Alamat Rumah Peserta
                            </label>
                            <input
                                type="text"
                                name="home_address"
                                value="{{ old('home_address', $participant->home_address) }}"
                                placeholder="Jalan, No. Rumah, Lingkungan / Kelurahan..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                            @error('home_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Kota / Kabupaten
                                </label>
                                <input
                                    type="text"
                                    name="city"
                                    value="{{ old('city', $participant->city ?: 'Padangsidimpuan') }}"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                    Kode Pos
                                </label>
                                <input
                                    type="text"
                                    name="postal_code"
                                    value="{{ old('postal_code', $participant->postal_code) }}"
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
                                    name="home_phone"
                                    value="{{ old('home_phone', $participant->home_phone) }}"
                                    placeholder="0634-..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Handphone / WhatsApp Peserta
                            </label>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone', $participant->phone) }}"
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
                                Nama Orang Tua (Ayah / Ibu / Wali)
                            </label>
                            <input
                                type="text"
                                name="parent_name"
                                value="{{ old('parent_name', $participant->parent_name) }}"
                                placeholder="Nama lengkap orang tua..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Pekerjaan Orang Tua
                            </label>
                            <input
                                type="text"
                                name="parent_job"
                                value="{{ old('parent_job', $participant->parent_job) }}"
                                placeholder="Contoh: PNS, Wiraswasta, Guru, TNI/Polri..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                            Alamat Rumah Orang Tua
                        </label>
                        <input
                            type="text"
                            name="parent_address"
                            value="{{ old('parent_address', $participant->parent_address) }}"
                            placeholder="Alamat domisili orang tua..."
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Kota / Kabupaten
                            </label>
                            <input
                                type="text"
                                name="parent_city"
                                value="{{ old('parent_city', $participant->parent_city ?: 'Padangsidimpuan') }}"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Kode Pos
                            </label>
                            <input
                                type="text"
                                name="parent_postal_code"
                                value="{{ old('parent_postal_code', $participant->parent_postal_code) }}"
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
                                name="parent_home_phone"
                                value="{{ old('parent_home_phone', $participant->parent_home_phone) }}"
                                placeholder="0634-..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                            Handphone / WhatsApp Orang Tua
                        </label>
                        <input
                            type="text"
                            name="parent_phone"
                            value="{{ old('parent_phone', $participant->parent_phone) }}"
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
                                <th class="py-2.5 px-3">Nama Sekolah</th>
                                <th class="py-2.5 px-3 w-44">Kota</th>
                                <th class="py-2.5 px-3 w-28">Tahun Lulus</th>
                                <th class="py-2.5 px-3 w-44">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($formalEducation as $i => $edu)
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $edu['level'] ?? '' }}
                                        <input type="hidden" name="formal_education[{{ $i }}][level]" value="{{ $edu['level'] ?? '' }}">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input
                                            type="text"
                                            name="formal_education[{{ $i }}][school_name]"
                                            value="{{ old("formal_education.$i.school_name", $edu['school_name'] ?? '') }}"
                                            placeholder="Nama {{ $edu['level'] ?? '' }}..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input
                                            type="text"
                                            name="formal_education[{{ $i }}][city]"
                                            value="{{ old("formal_education.$i.city", $edu['city'] ?? 'Padangsidimpuan') }}"
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input
                                            type="text"
                                            name="formal_education[{{ $i }}][graduation_year]"
                                            value="{{ old("formal_education.$i.graduation_year", $edu['graduation_year'] ?? '') }}"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input
                                            type="text"
                                            name="formal_education[{{ $i }}][notes]"
                                            value="{{ old("formal_education.$i.notes", $edu['notes'] ?? '') }}"
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
                            name="scout_siaga[year]"
                            value="{{ old('scout_siaga.year', $scoutSiaga['year'] ?? '') }}"
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
                            name="scout_siaga[group_number]"
                            value="{{ old('scout_siaga.group_number', $scoutSiaga['group_number'] ?? '') }}"
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
                            name="scout_siaga[pangkalan]"
                            value="{{ old('scout_siaga.pangkalan', $scoutSiaga['pangkalan'] ?? '') }}"
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
                                        name="scout_siaga[levels][{{ $key }}]"
                                        value="{{ old("scout_siaga.levels.$key", $scoutSiaga['levels'][$key] ?? '') }}"
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
                                        name="scout_siaga[activities][{{ $i }}]"
                                        value="{{ old("scout_siaga.activities.$i", $scoutSiaga['activities'][$i] ?? '') }}"
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
                                            name="scout_siaga[tkk][{{ $i }}]"
                                            value="{{ old("scout_siaga.tkk.$i", $scoutSiaga['tkk'][$i] ?? '') }}"
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
                                            name="scout_siaga[tkk][{{ $i }}]"
                                            value="{{ old("scout_siaga.tkk.$i", $scoutSiaga['tkk'][$i] ?? '') }}"
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
                            name="scout_penggalang[year]"
                            value="{{ old('scout_penggalang.year', $scoutPenggalang['year'] ?? date('Y')) }}"
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
                            name="scout_penggalang[group_number]"
                            value="{{ old('scout_penggalang.group_number', $scoutPenggalang['group_number'] ?: $patrol->group_number) }}"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">
                            Pangkalan Penggalang
                        </label>
                        <input
                            type="text"
                            name="scout_penggalang[pangkalan]"
                            value="{{ old('scout_penggalang.pangkalan', $scoutPenggalang['pangkalan'] ?: $scoutGroup->school_name) }}"
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
                                    name="scout_penggalang[levels][{{ $key }}]"
                                    value="{{ old("scout_penggalang.levels.$key", $scoutPenggalang['levels'][$key] ?? '') }}"
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
                                    name="scout_penggalang[activities][{{ $i }}]"
                                    value="{{ old("scout_penggalang.activities.$i", $scoutPenggalang['activities'][$i] ?? '') }}"
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
                                            name="scout_penggalang[tkk_purwa][{{ $i }}]"
                                            value="{{ old("scout_penggalang.tkk_purwa.$i", $scoutPenggalang['tkk_purwa'][$i] ?? '') }}"
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
                                            name="scout_penggalang[tkk_madya][{{ $i }}]"
                                            value="{{ old("scout_penggalang.tkk_madya.$i", $scoutPenggalang['tkk_madya'][$i] ?? '') }}"
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
                                            name="scout_penggalang[tkk_utama][{{ $i }}]"
                                            value="{{ old("scout_penggalang.tkk_utama.$i", $scoutPenggalang['tkk_utama'][$i] ?? '') }}"
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

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('scout-group.patrols') }}" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300">
                    Batal
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-amber-600 px-6 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700"
                >
                    Perbarui Biodata Peserta
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
