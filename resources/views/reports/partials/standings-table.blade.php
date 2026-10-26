{{-- Tabel peringkat regu berprestasi untuk PDF. Variabel: $rows, $emptyText, $labels (tiga label perolehan) --}}
<table class="data">
    <thead>
        <tr>
            <th class="center" style="width: 28px;">Rank</th>
            <th class="center" style="width: 52px;">NPP</th>
            <th class="center">Regu &amp; Pangkalan</th>
            <th class="center" style="width: 46px;">Gudep</th>
            <th class="center">Kwarran</th>
            <th class="center">{{ $labels[0] }}</th>
            <th class="center">{{ $labels[1] }}</th>
            <th class="center">{{ $labels[2] }}</th>
            <th class="center">Predikat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $st)
            <tr>
                <td class="center">{{ $st['rank'] }}</td>
                <td class="center strong" style="white-space: nowrap;">{{ $st['npp'] ?? '-' }}</td>
                <td>
                    <span class="strong">{{ $st['patrol_name'] }}</span><br>
                    <span class="muted">{{ $st['school_name'] }}</span>
                </td>
                <td class="center" style="white-space: nowrap;">{{ $st['group_number'] }}</td>
                <td>{{ $st['kwarran_name'] }}</td>
                <td class="center strong">{{ $st['gold'] }}</td>
                <td class="center strong">{{ $st['silver'] }}</td>
                <td class="center strong">{{ $st['bronze'] }}</td>
                <td class="center strong">{{ preg_replace('/\s+(Putra|Putri)$/u', '', $st['general_rank_title'] ?? 'Peringkat '.$st['rank']) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="center muted" style="padding: 14px; font-style: italic;">{{ $emptyText }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
