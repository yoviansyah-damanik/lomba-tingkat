{{-- Tabel peringkat dan rekap nilai per mata lomba untuk PDF --}}
<table class="data">
    <thead>
        <tr>
            <th class="center" style="width: 28px;">Rank</th>
            <th class="center" style="width: 48px;">NPP</th>
            <th class="center">Regu &amp; Pangkalan</th>
            <th class="center" style="width: 44px;">Gudep</th>
            <th class="center">Kwarran</th>
            <th class="center" style="width: 60px;">Nilai Akhir</th>
            <th class="center" style="width: 96px;">Predikat Prestasi</th>
            <th class="center" style="width: 36px;">Poin</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $score)
            @php
                $isMasked = $score->patrol?->is_masked ?? false;
                $patrolName = $score->patrol?->display_name ?? $score->patrol?->patrol_name ?? 'Regu';
                $schoolName = $score->patrol?->display_school ?? $score->patrol?->scoutGroup?->school_name ?? '-';
                $groupNumber = $isMasked ? '***' : ($score->patrol?->group_number ?? '-');
                $kwarran = $isMasked ? 'Dirahasiakan' : ($score->patrol?->scoutGroup?->kwarran_name ?? 'Padangsidimpuan');
                $points = \App\Models\CompetitionScore::rankWeight($score->rank, $score->predicate);
            @endphp
            <tr class="{{ $score->rank && $score->rank <= 3 ? 'highlight' : '' }}">
                <td class="center strong">
                    @if($score->rank === 1)
                        🥇 1
                    @elseif($score->rank === 2)
                        🥈 2
                    @elseif($score->rank === 3)
                        🥉 3
                    @else
                        {{ $score->rank ?: '-' }}
                    @endif
                </td>
                <td class="center strong" style="white-space: nowrap;">{{ $score->patrol?->npp ?? '-' }}</td>
                <td>
                    <span class="strong">{{ $patrolName }}</span><br>
                    <span class="muted">{{ $schoolName }}</span>
                </td>
                <td class="center" style="white-space: nowrap;">{{ $groupNumber }}</td>
                <td>{{ $kwarran }}</td>
                <td class="center strong" style="font-family: monospace;">{{ number_format((float) $score->final_score, 2) }}</td>
                <td class="center strong">{{ $score->predicateLabel() ?: '-' }}</td>
                <td class="center strong" style="color: #065f46;">{{ $points }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center muted" style="padding: 14px; font-style: italic;">{{ $emptyText }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
