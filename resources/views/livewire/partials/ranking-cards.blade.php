{{-- Kartu Peringkat Umum regu (disensor selama blind mode aktif) --}}
@foreach($rankings as $ranking)
    <x-stat-card
        label="Peringkat Umum"
        :value="$ranking['censored'] ? '#•••' : ($ranking['rank'] ? '#'.$ranking['rank'] : '-')"
        :description="$ranking['censored'] ? 'Disensor selama blind mode aktif' : ($ranking['rank'] ? 'Regu '.$ranking['patrol'].' dari '.$ranking['total'].' regu '.strtolower($ranking['category']) : 'Regu '.$ranking['patrol'].' · belum ada nilai')"
        color="amber"
        icon="trophy"
        action-text="Lihat Penilaian Regu"
        href="{{ route('team-assessment.index') }}"
    />
@endforeach
