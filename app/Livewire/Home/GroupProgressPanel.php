<?php

namespace App\Livewire\Home;

use App\Models\ScoutGroup;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel dasbor Gugus Depan: kemajuan kelengkapan pendaftaran dan langkah berikutnya
 */
#[Lazy]
class GroupProgressPanel extends Component
{
    public function placeholder(): string
    {
        return '<div class="h-56 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    /**
     * Daftar langkah pendaftaran beserta statusnya
     *
     * @return array<int, array{label: string, done: bool, detail: string, url: string}>
     */
    private function steps(?ScoutGroup $group): array
    {
        if (! $group) {
            return [];
        }

        $group->loadMissing(['patrols.participants', 'chaperones']);

        $rosterProblems = $group->patrolRosterProblems();
        $chaperoneReady = $group->chaperones->isNotEmpty() && $group->missingChaperoneGenders() === [] && $group->patrols->every(fn ($p) => $p->chaperone_id);

        return [
            [
                'label' => 'Regu terbentuk',
                'done' => $group->patrols->isNotEmpty(),
                'detail' => $group->patrols->count().' regu terdaftar',
                'url' => route('scout-group.patrols'),
            ],
            [
                'label' => 'Setiap regu 8 peserta dengan Pinru & Wapinru',
                'done' => $group->patrols->isNotEmpty() && $rosterProblems === [],
                'detail' => $rosterProblems === [] ? 'Susunan regu lengkap' : count($rosterProblems).' hal perlu dilengkapi',
                'url' => route('scout-group.patrols'),
            ],
            [
                'label' => 'Pembina pendamping sesuai regu',
                'done' => $chaperoneReady,
                'detail' => $group->chaperones->count().' bindamping terdaftar',
                'url' => route('scout-group.chaperones'),
            ],
            [
                'label' => 'Berkas administrasi (Surat Tugas & Bukti Bayar)',
                'done' => filled($group->mandate_letter_path) && filled($group->payment_proof_path),
                'detail' => (filled($group->mandate_letter_path) ? 'Surat tugas ✓' : 'Surat tugas belum').' · '.(filled($group->payment_proof_path) ? 'Bukti bayar ✓' : 'Bukti bayar belum'),
                'url' => route('scout-group.review'),
            ],
        ];
    }

    public function render()
    {
        $group = ScoutGroup::where('user_id', auth()->id())->first();
        $steps = $this->steps($group);
        $done = collect($steps)->where('done', true)->count();

        return view('livewire.home.group-progress-panel', [
            'group' => $group,
            'steps' => $steps,
            'done' => $done,
            'total' => count($steps),
        ]);
    }
}
