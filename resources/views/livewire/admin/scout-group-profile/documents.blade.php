{{-- Berkas Unggahan Pangkalan --}}
<section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <h3 class="mb-4 text-sm font-bold text-zinc-900 dark:text-white">Berkas Unggahan Pangkalan</h3>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach([['Surat Tugas / Mandat Gugus Depan', $g->mandate_letter_path], ['Bukti Pembayaran Pendaftaran', $g->payment_proof_path]] as [$label, $path])
            <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-100 p-4 dark:border-zinc-800">
                <div>
                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</div>
                    <div class="text-[11px] {{ $path ? 'text-emerald-600' : 'text-zinc-400' }}">{{ $path ? 'Sudah diunggah' : 'Belum diunggah' }}</div>
                </div>
                @if($path)
                    <x-file-viewer-trigger :path="$path" :title="$label.' — '.$g->school_name" label="Lihat" />
                @endif
            </div>
        @endforeach
    </div>
</section>
