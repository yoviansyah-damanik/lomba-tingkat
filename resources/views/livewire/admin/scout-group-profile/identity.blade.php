{{-- Identitas Pangkalan --}}
<section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <h3 class="mb-4 text-sm font-bold text-zinc-900 dark:text-white">Identitas Pangkalan</h3>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-zinc-400">Nama Pangkalan</dt><dd class="font-semibold text-zinc-900 dark:text-white">{{ $g->school_name }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Kode Registrasi</dt><dd class="font-mono font-semibold text-amber-700 dark:text-amber-400">{{ $g->registration_code }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Kwarran</dt><dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->kwarran_name ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Gudep Putra</dt><dd class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $g->male_group_number ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Gudep Putri</dt><dd class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $g->female_group_number ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Terverifikasi</dt><dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->verified_at ? $g->verified_at->translatedFormat('d F Y, H:i').' WIB' : '-' }}</dd></div>
        <div class="sm:col-span-2 lg:col-span-3"><dt class="text-xs text-zinc-400">Alamat</dt><dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->address ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Penanggung Jawab (PIC)</dt><dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->contact_person ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Kontak PIC</dt><dd class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $g->contact_phone ?: '-' }}</dd></div>
        <div><dt class="text-xs text-zinc-400">Username Akun</dt><dd class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $g->user?->username ?: '-' }}</dd></div>
    </dl>
</section>
