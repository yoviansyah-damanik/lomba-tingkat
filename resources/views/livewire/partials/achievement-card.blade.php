{{-- Kartu jumlah prestasi yang diperoleh regu (Prestasi Tinggi, Baik, Cukup) --}}
<x-stat-card
    label="Prestasi Diperoleh"
    :value="$achievements['total'] ?? 0"
    unit="Prestasi"
    :description="'Tinggi '.($achievements['high'] ?? 0).' · Baik '.($achievements['good'] ?? 0).' · Cukup '.($achievements['fair'] ?? 0)"
    color="emerald"
    icon="star"
    action-text="Lihat Penilaian Regu"
    href="{{ route('team-assessment.index') }}"
/>
