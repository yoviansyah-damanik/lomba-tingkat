<div class="bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-5">
        <div class="w-full max-w-[480px]">
            <div class="bg-slate-800 rounded-[20px] overflow-hidden shadow-2xl border border-slate-700 relative">
                @if($data)
                    {{-- Valid QR Code Header --}}
                    <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-emerald-500 p-6 text-center relative">
                        <div class="w-[60px] h-[60px] rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3 border-2 border-white/40">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-[16px] font-extrabold tracking-wide uppercase text-white">{{ $data['title'] }}</div>
                        <div class="text-[12px] text-white/90 mt-1">{{ $data['status_label'] }}</div>
                    </div>

                    <div class="p-6">
                        {{-- Profile Header --}}
                        <div class="flex items-center gap-4 pb-5 border-b border-slate-700 mb-5">
                            <div class="w-[72px] h-[90px] rounded-[10px] overflow-hidden border-2 border-amber-600 bg-slate-900 shrink-0 flex items-center justify-center">
                                @if($data['photo_path'])
                                    <img src="{{ asset('storage/' . $data['photo_path']) }}" alt="{{ $data['name'] }}" class="w-full h-full object-cover block">
                                @else
                                    <div class="text-[10px] text-slate-500 text-center font-bold">
                                        FOTO RESMI
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-md uppercase bg-amber-500/15 text-amber-400 border border-amber-500/30 mb-1.5">{{ $data['category_label'] }}</span>
                                <div class="text-[17px] font-extrabold text-white leading-tight break-words">{{ $data['name'] }}</div>
                                <div class="text-[13px] text-slate-400 mt-0.5 font-medium">{{ $data['role_label'] }}</div>
                            </div>
                        </div>

                        {{-- Data Details --}}
                        <table class="w-full border-collapse text-[13px]">
                            @if(!empty($data['id_number']))
                                <tr class="border-b border-slate-700/60">
                                    <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Nomor ID Resmi</td>
                                    <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">
                                        <span class="text-amber-400 font-mono text-[14px] bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/30">{{ $data['id_number'] }}</span>
                                    </td>
                                </tr>
                            @endif
                            <tr class="border-b border-slate-700/60">
                                <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Pangkalan / Sekolah</td>
                                <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['school_name'] }}</td>
                            </tr>
                            @if($data['type'] === 'participant')
                                <tr class="border-b border-slate-700/60">
                                    <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Nama Regu</td>
                                    <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['patrol_name'] }}</td>
                                </tr>
                            @endif
                            <tr class="border-b border-slate-700/60">
                                <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Nomor Gudep</td>
                                <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['group_number'] }}</td>
                            </tr>
                            <tr class="border-b border-slate-700/60">
                                <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Kwartir Ranting</td>
                                <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['kwarran_name'] }}</td>
                            </tr>
                            <tr class="border-b border-slate-700/60">
                                <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">NTA Pramuka</td>
                                <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['scout_id'] }}</td>
                            </tr>
                            @if($data['type'] === 'participant')
                                <tr class="border-b border-slate-700/60">
                                    <td class="py-2.5 px-1 align-top text-slate-400 w-[38%] font-medium">Golongan Darah</td>
                                    <td class="py-2.5 px-1 align-top text-slate-100 w-[62%] font-semibold">{{ $data['blood_type'] }}</td>
                                </tr>
                            @endif
                        </table>

                        {{-- Security Seal & Token Info --}}
                        @if($qrDataUri)
                            <div class="mt-5 bg-slate-900/70 border border-dashed border-slate-600 rounded-xl p-3.5 flex items-center gap-3.5">
                                <img src="{{ $qrDataUri }}" alt="QR" class="w-16 h-16 shrink-0 rounded-lg bg-white p-1">
                                <div class="text-[11px] text-slate-400 leading-relaxed">
                                    <strong class="text-sky-400 block mb-0.5">VALIDASI OTENTIKASI SISTEM</strong>
                                    Token ini terdaftar secara resmi di pangkalan data Kwartir Cabang Kota Padangsidimpuan.
                                    <span class="font-mono text-[10px] text-slate-300 break-all mt-1 block">UUID: {{ $data['token'] }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Invalid QR Code --}}
                    <div class="bg-gradient-to-br from-rose-900 via-rose-700 to-rose-500 p-6 text-center relative">
                        <div class="w-[60px] h-[60px] rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3 border-2 border-white/40">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="text-[16px] font-extrabold tracking-wide uppercase text-white">QR Code Tidak Terdaftar</div>
                        <div class="text-[12px] text-white/90 mt-1">Sistem tidak menemukan data kredensial aktif</div>
                    </div>

                    <div class="p-8 text-center">
                        <p class="text-[13px] text-slate-300 leading-relaxed">
                            Kode verifikasi yang Anda pindai tidak valid atau belum terbit di basis data LT III Tahun 2026.
                        </p>
                        <p class="text-[11px] text-slate-500 mt-2.5 font-mono">
                            Token: {{ $token }}
                        </p>
                    </div>
                @endif

                <div class="bg-slate-900 p-5 border-t border-slate-700 text-center text-[11px] text-slate-400">
                    <div>Panitia Pelaksana Lomba Tingkat III (LT III) Tahun 2026</div>
                    <div>Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan</div>
                    <a href="{{ route('home') }}" class="inline-block mt-2.5 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-[12px] transition">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
</div>
