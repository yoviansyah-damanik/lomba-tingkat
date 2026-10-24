<x-layouts::app title="Formulir Biodata Pembina Pendamping">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <x-official-header
                :title="'Formulir Biodata Pembina Pendamping: ' . $scoutGroup->school_name"
                :subtitle="'Kategori: ' . ($gender === 'male' ? 'Putra' : 'Putri') . ' • Gudep ' . ($gender === 'male' ? ($scoutGroup->male_group_number ?? $scoutGroup->group_number) : ($scoutGroup->female_group_number ?? $scoutGroup->group_number)) . ' • ' . $scoutGroup->school_name"
            />
            <a href="{{ route('scout-group.chaperones') }}" class="inline-flex items-center gap-1.5 self-start rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                &larr; Kembali ke Pembina Pendamping
            </a>
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

        <form method="POST" action="{{ route('scout-group.chaperones.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. IDENTITAS DIRI & PAS FOTO --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <div class="mb-6 flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 1</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Identitas Diri & Pas Foto</h2>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        Bindamping {{ $gender === 'male' ? 'Putra' : 'Putri' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-b border-zinc-200 pb-4 dark:border-zinc-800 text-xs">
                    <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/60">
                        <span class="text-zinc-500 block uppercase font-semibold text-[10px] tracking-wider">Kwartir Daerah</span>
                        <strong class="text-sm font-bold text-zinc-900 dark:text-zinc-100">SUMATERA UTARA</strong>
                    </div>
                    <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/60">
                        <span class="text-zinc-500 block uppercase font-semibold text-[10px] tracking-wider">Kwartir Cabang</span>
                        <strong class="text-sm font-bold text-zinc-900 dark:text-zinc-100">KOTA Padangsidimpuan</strong>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-700/50">
                        <span class="text-amber-800 dark:text-amber-300 block uppercase font-semibold text-[10px] tracking-wider">Nomor Gudep Pangkalan</span>
                        <strong class="text-sm font-bold text-amber-950 dark:text-amber-200">
                            {{ $gender === 'male' ? ($scoutGroup->male_group_number ?? $scoutGroup->group_number) : ($scoutGroup->female_group_number ?? $scoutGroup->group_number) }}
                        </strong>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-12">
                    {{-- Form Kiri: Data Identitas Diri --}}
                    <div class="lg:col-span-9 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nama Lengkap & Gelar <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                    placeholder="Nama lengkap beserta gelar..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Kategori Pembina Pendamping <span class="text-red-500">*</span>
                                </label>
                                @php($chaperoneGender = $gender)
                                <input type="hidden" name="gender" value="{{ $chaperoneGender }}">
                                <input type="text" value="{{ $chaperoneGender === 'male' ? 'Putra (Bindamping Putra)' : 'Putri (Bindamping Putri)' }}" readonly
                                    class="w-full rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 py-2 text-xs sm:text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400">
                                <p class="mt-1 text-[11px] text-zinc-400">Kategori mengikuti slot bindamping dan tidak dapat diubah.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tempat Lahir <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="birth_place" required value="{{ old('birth_place') }}"
                                    placeholder="Kota / Tempat Lahir..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tanggal Lahir (Tgl/Bln/Th) <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="birth_date" required value="{{ old('birth_date') }}"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Agama <span class="text-red-500">*</span>
                                </label>
                                <select name="religion" required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">-- Pilih Agama --</option>
                                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $rel)
                                        <option value="{{ $rel }}" @selected(old('religion') === $rel)>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Golongan Darah <span class="text-red-500">*</span>
                                </label>
                                <select name="blood_type" required
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">-- Pilih --</option>
                                    @foreach(\App\Support\BloodType::OPTIONS as $bt => $btLabel)
                                        <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $btLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    NTA (Nomor Tanda Anggota) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="scout_id" required value="{{ old('scout_id') }}"
                                    placeholder="Contoh: 02.21.10.001"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                Alamat Rumah Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="home_address" required value="{{ old('home_address') }}"
                                placeholder="Jalan, No. Rumah, Lingkungan / Kelurahan..."
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Kota / Kabupaten <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="city" required value="{{ old('city', 'Kota Padangsidimpuan') }}"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Kode Pos <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="postal_code" required value="{{ old('postal_code') }}"
                                    placeholder="22727"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Telepon Rumah <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="home_phone" required value="{{ old('home_phone') }}"
                                    placeholder="Contoh: (0634) 21xxx"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
                                    Handphone / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                                    placeholder="Contoh: 081234567890"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                        </div>
                    </div>

                    {{-- Form Kanan: Pas Foto 3 x 4 --}}
                    <div class="lg:col-span-3 flex flex-col items-center justify-start rounded-xl border border-dashed border-zinc-300 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/40 text-center"
                        x-data="{ photoPreview: null }">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                            Pas Foto 3 x 4 <span class="text-red-500">*</span>
                        </span>
                        
                        <div class="w-32 h-44 rounded-lg border-2 border-zinc-300 bg-white shadow-inner flex items-center justify-center overflow-hidden dark:border-zinc-600 dark:bg-zinc-800 mb-3 relative">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover" alt="Preview Foto">
                            </template>
                            <template x-if="!photoPreview">
                                <div class="text-zinc-400 text-center p-2">
                                    <flux:icon name="user" class="size-10 mx-auto mb-1 text-zinc-300 dark:text-zinc-600" />
                                    <span class="text-[11px] font-semibold block">Ukuran 3 x 4</span>
                                    <span class="text-[9px] text-zinc-400 block">Seragam Pramuka Lengkap</span>
                                </div>
                            </template>
                        </div>

                        <label class="cursor-pointer rounded-lg bg-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-800 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                            Unggah Foto
                            <input type="file" name="photo" accept="image/*" class="hidden"
                                @change="
                                    const file = $event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => { photoPreview = e.target.result; };
                                        reader.readAsDataURL(file);
                                    }
                                " />
                        </label>
                        <p class="text-[10px] text-zinc-400 mt-1">Maks. 2MB (JPG, PNG)</p>
                    </div>
                </div>
            </div>

            {{-- Tabel 1: PENDIDIKAN FORMAL --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 2</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Pendidikan Formal</h2>
                        <p class="text-xs text-zinc-400">Riwayat jenjang pendidikan SD s/d Pascasarjana</p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 bg-zinc-50/75 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-3 py-2.5 font-bold w-24">Sekolah</th>
                                <th class="px-3 py-2.5 font-bold">Nama Sekolah / Perguruan Tinggi</th>
                                <th class="px-3 py-2.5 font-bold w-36">Kota</th>
                                <th class="px-3 py-2.5 font-bold w-24">Tahun Lulus</th>
                                <th class="px-3 py-2.5 font-bold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($defaultEducation as $idx => $edu)
                                <tr>
                                    <td class="px-3 py-2 font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $edu['level'] }}
                                        <input type="hidden" name="formal_education[{{ $idx }}][level]" value="{{ $edu['level'] }}" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="formal_education[{{ $idx }}][school_name]" value="{{ old("formal_education.{$idx}.school_name", $edu['school_name'] ?? '') }}"
                                            placeholder="Nama {{ $edu['level'] }}..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="formal_education[{{ $idx }}][city]" value="{{ old("formal_education.{$idx}.city", $edu['city'] ?? '') }}"
                                            placeholder="Kota..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="formal_education[{{ $idx }}][graduation_year]" value="{{ old("formal_education.{$idx}.graduation_year", $edu['graduation_year'] ?? '') }}"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="formal_education[{{ $idx }}][notes]" value="{{ old("formal_education.{$idx}.notes", $edu['notes'] ?? '') }}"
                                            placeholder="Jurusan / Ket..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel 2: KEPRAMUKAAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 3</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Kepramukaan</h2>
                    <p class="text-xs text-zinc-400">Riwayat golongan kepramukaan yang pernah dilalui</p>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 bg-zinc-50/75 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-3 py-2.5 font-bold w-36">Nama Golongan</th>
                                <th class="px-3 py-2.5 font-bold w-24">Tahun</th>
                                <th class="px-3 py-2.5 font-bold w-32">Nomor Gudep</th>
                                <th class="px-3 py-2.5 font-bold">Pangkalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($defaultExperiences as $idx => $exp)
                                <tr>
                                    <td class="px-3 py-2 font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $idx + 1 }}. {{ $exp['level'] }}
                                        <input type="hidden" name="scout_experiences[{{ $idx }}][level]" value="{{ $exp['level'] }}" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_experiences[{{ $idx }}][year]" value="{{ old("scout_experiences.{$idx}.year", $exp['year'] ?? '') }}"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_experiences[{{ $idx }}][group_number]" value="{{ old("scout_experiences.{$idx}.group_number", $exp['group_number'] ?? '') }}"
                                            placeholder="No. Gudep..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_experiences[{{ $idx }}][base]" value="{{ old("scout_experiences.{$idx}.base", $exp['base'] ?? '') }}"
                                            placeholder="Nama Pangkalan / Gugus Depan..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel 3: KURSUS / PELATIHAN KEPRAMUKAAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 4</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Kursus / Pelatihan Kepramukaan</h2>
                    <p class="text-xs text-zinc-400">Kursus kualifikasi pembina (KMD, KML, KPD, KPL)</p>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 bg-zinc-50/75 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-3 py-2.5 font-bold w-36">Nama Kursus</th>
                                <th class="px-3 py-2.5 font-bold w-24">Tahun</th>
                                <th class="px-3 py-2.5 font-bold w-48">Tempat</th>
                                <th class="px-3 py-2.5 font-bold">Keterangan / Nomor Ijazah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($defaultTrainings as $idx => $trn)
                                <tr>
                                    <td class="px-3 py-2 font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $idx + 1 }}. {{ $trn['course'] }}
                                        <input type="hidden" name="scout_trainings[{{ $idx }}][course]" value="{{ $trn['course'] }}" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_trainings[{{ $idx }}][year]" value="{{ old("scout_trainings.{$idx}.year", $trn['year'] ?? '') }}"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_trainings[{{ $idx }}][location]" value="{{ old("scout_trainings.{$idx}.location", $trn['location'] ?? '') }}"
                                            placeholder="Kwarcab / Pusdiklat..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="scout_trainings[{{ $idx }}][notes]" value="{{ old("scout_trainings.{$idx}.notes", $trn['notes'] ?? '') }}"
                                            placeholder="Keterangan / No. Ijazah..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel 4: KURSUS / PELATIHAN DI LUAR KEPRAMUKAAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8"
                x-data="{
                    rows: {{ json_encode(old('other_trainings', [['name' => '', 'year' => '', 'location' => '', 'notes' => '']])) }},
                    addRow() {
                        this.rows.push({ name: '', year: '', location: '', notes: '' });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        }
                    }
                }">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Bagian 5</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Kursus / Pelatihan di Luar Kepramukaan</h2>
                        <p class="text-xs text-zinc-400">Pelatihan kepemimpinan, keguruan, teknis atau keahlian umum lainnya</p>
                    </div>
                    <button type="button" @click="addRow()" class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                        + Tambah Baris
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 bg-zinc-50/75 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-3 py-2.5 font-bold">Nama Kursus / Pelatihan</th>
                                <th class="px-3 py-2.5 font-bold w-24">Tahun</th>
                                <th class="px-3 py-2.5 font-bold w-48">Tempat</th>
                                <th class="px-3 py-2.5 font-bold">Keterangan</th>
                                <th class="px-3 py-2.5 font-bold w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <template x-for="(row, idx) in rows" :key="idx">
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'other_trainings[' + idx + '][name]'" x-model="row.name"
                                            placeholder="Nama pelatihan / workshop..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'other_trainings[' + idx + '][year]'" x-model="row.year"
                                            placeholder="Tahun..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'other_trainings[' + idx + '][location]'" x-model="row.location"
                                            placeholder="Penyelenggara / Kota..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'other_trainings[' + idx + '][notes]'" x-model="row.notes"
                                            placeholder="Sertifikat / Ket..."
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs text-zinc-900 focus:border-amber-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeRow(idx)" class="text-red-500 hover:text-red-700">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('scout-group.chaperones') }}" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-amber-600 px-6 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700">
                    Simpan Biodata Bindamping
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
