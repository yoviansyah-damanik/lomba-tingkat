<div class="flex w-full flex-col items-center">
    @php
        $theme = $staff->isJudge() ? 'judge' : 'staff';
        $photoUrl = $staff->avatar_path ? asset('storage/' . $staff->avatar_path) : null;
        $bodyBg = $staff->isJudge() ? 'bg-[#1c0d02]' : 'bg-[#1a0505]';
        $roleName =
            $staff->isJudge() && $staff->isCommittee()
                ? 'PANITIA PELAKSANA & DEWAN JURI'
                : ($staff->isJudge()
                    ? 'DEWAN JURI PERLOMBAAN'
                    : 'PANITIA PELAKSANA');
    @endphp

        {{-- Toolbar Aksi --}}
        <x-digital-card.toolbar :subtitle="'Aparatur: ' . $staff->name" :download-filename="'KARTU_APARATUR_' . Str::slug($staff->name)" />

        @include('digital-cards.partials.staff-card')
</div>
