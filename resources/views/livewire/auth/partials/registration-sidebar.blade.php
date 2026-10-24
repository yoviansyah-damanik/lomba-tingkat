<div class="space-y-6 lg:col-span-4 lg:sticky lg:top-24">
    {{-- Card Alur Sistem Pendaftaran --}}
    <div class="rounded-2xl border border-amber-300/70 bg-gradient-to-br from-amber-50/90 to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-zinc-900 dark:to-zinc-900/90">
        <div class="flex items-center gap-3">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white font-bold shadow-md shadow-amber-600/20">
                <flux:icon name="identification" class="size-5" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Alur Pendaftaran Kontingen</h3>
                <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">LT III Kwarcab Padangsidimpuan</span>
            </div>
        </div>

        <ol class="mt-5 space-y-3.5 text-xs">
            <li class="flex items-start gap-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-[11px] font-bold text-white shadow-xs">1</span>
                <div>
                    <strong class="block font-semibold text-zinc-900 dark:text-zinc-100">Kode Undangan & Registrasi Akun Gudep</strong>
                    <span class="text-zinc-500">Masukkan kode undangan dari panitia, lalu lengkapi akun PIC penanggung jawab.</span>
                </div>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">2</span>
                <div>
                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Input Regu & Peserta di Dasbor</strong>
                    <span class="text-zinc-500">Bentuk Regu Putra/Putri dan entri biodata peserta di dalam <em>Dasbor Gudep</em>.</span>
                </div>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">3</span>
                <div>
                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Input Bindamping di Dasbor</strong>
                    <span class="text-zinc-500">Entri data Pembina Pendamping putra/putri di dalam <em>Dasbor Gudep</em>.</span>
                </div>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-[11px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">4</span>
                <div>
                    <strong class="block font-semibold text-zinc-800 dark:text-zinc-200">Verifikasi Berkas oleh Administrator</strong>
                    <span class="text-zinc-500">Pemeriksaan & persetujuan kelengkapan kontingen oleh Administrator serta penerbitan kartu digital QR Code.</span>
                </div>
            </li>
        </ol>

        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-100/50 p-3 text-xs text-amber-950 dark:border-amber-500/20 dark:bg-amber-950/40 dark:text-amber-200">
            <strong class="font-bold flex items-center gap-1.5 mb-1">
                <flux:icon name="information-circle" class="size-4 text-amber-700 dark:text-amber-400" />
                Verifikasi Administrator:
            </strong>
            <p class="text-[11px] leading-relaxed">
                Pendaftaran akun pangkalan <strong>tidak memerlukan email</strong>. Akun langsung aktif dan dapat digunakan. Seluruh data kontingen (regu, peserta, bindamping) selanjutnya <strong>diverifikasi langsung oleh Administrator</strong>.
            </p>
        </div>
    </div>

    @php
        $paymentConfig = \App\Models\Event::activePaymentConfig();
    @endphp

    {{-- Card Ketentuan Biaya Pendaftaran per Regu --}}
    <div class="rounded-2xl border border-amber-300/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Biaya Pendaftaran Kontingen</h4>
            <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">Per Regu</span>
        </div>
        <div class="my-3">
            <span class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">{{ $paymentConfig['formatted_fee_per_patrol'] }}</span>
            <span class="text-xs text-zinc-500">/ regu</span>
            <p class="mt-1 text-[11px] text-zinc-500 leading-normal">
                Dihitung berdasarkan jumlah regu (Putra/Putri) yang didaftarkan. Pembayaran ditransfer ke rekening resmi Kwarcab saat pengajuan berkas di dasbor.
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block">Rekening Resmi Kwarcab:</span>
            <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-xs">{{ $paymentConfig['bank_name'] }}</div>
            <div class="font-mono font-bold text-amber-800 dark:text-amber-400 text-xs tracking-wide select-all">{{ $paymentConfig['account_number'] }}</div>
            <div class="text-[10px] text-zinc-600 dark:text-zinc-400 leading-tight">{{ $paymentConfig['account_holder'] }}</div>
        </div>
    </div>

    {{-- Card Ketentuan Pangkalan --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Ketentuan Pangkalan</h4>
        <ul class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
            <li class="flex items-start gap-2">
                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                <span>Pangkalan resmi SMP/MTs sederajat di 6 Kwartir Ranting Kota Padangsidimpuan.</span>
            </li>
            <li class="flex items-start gap-2">
                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                <span>Pendaftaran hanya untuk gugus depan yang telah diizinkan panitia; setiap gudep memperoleh <strong>1 kode undangan sekali pakai</strong>.</span>
            </li>
            <li class="flex items-start gap-2">
                <flux:icon name="check" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                <span>Nomor WhatsApp aktif digunakan untuk konfirmasi verifikasi dan pemberitahuan pos lomba.</span>
            </li>
        </ul>

        <div class="mt-4 border-t border-zinc-100 pt-3 text-xs dark:border-zinc-800">
            <span class="text-zinc-500">Sudah memiliki akun pangkalan?</span>
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-amber-700 hover:underline dark:text-amber-400">
                Masuk ke Dasbor &rarr;
            </a>
        </div>

        <div class="mt-3">
            <flux:button href="{{ route('guide') }}" wire:navigate icon="book-open-text" variant="subtle" size="sm" class="w-full">
            Baca Panduan Pendaftaran &amp; Penggunaan
        </flux:button>
        </div>
    </div>
</div>
