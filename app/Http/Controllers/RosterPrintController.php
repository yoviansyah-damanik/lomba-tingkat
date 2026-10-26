<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\ScoutGroup;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Cetak PDF daftar Pangkalan Gudep, Peserta (satu gudep per halaman), dan Bindamping.
 * Setiap dokumen dapat dibatasi pada satu gudep dengan query ?gudep={id}.
 */
class RosterPrintController extends Controller
{
    private const DOCUMENTS = [
        'scout-groups' => ['title' => 'Daftar Pangkalan Gugus Depan', 'view' => 'reports.scout-groups-pdf', 'file' => 'daftar-pangkalan-gudep'],
        'participants' => ['title' => 'Daftar Peserta per Gugus Depan', 'view' => 'reports.participants-pdf', 'file' => 'daftar-peserta'],
        'chaperones' => ['title' => 'Daftar Pembina Pendamping (Bindamping)', 'view' => 'reports.chaperones-pdf', 'file' => 'daftar-bindamping'],
    ];

    /**
     * Pratinjau PDF dalam halaman dengan toolbar unduh
     */
    public function preview(Request $request, string $document): View
    {
        $meta = $this->meta($document);
        $query = array_filter(['gudep' => $request->query('gudep')]);

        return view('reports.pdf-preview', [
            'title' => $meta['title'],
            'pdfUrl' => route('admin.print.pdf', ['document' => $document, 'raw' => 1] + $query),
            'downloadUrl' => route('admin.print.pdf', ['document' => $document, 'download' => 1] + $query),
            'backUrl' => url()->previous(route('admin.reports.index')),
        ]);
    }

    /**
     * Berkas PDF
     */
    public function pdf(Request $request, PdfService $pdf, string $document): Response
    {
        $meta = $this->meta($document);
        $groupId = $request->query('gudep');

        $data = match ($document) {
            'scout-groups' => ['groups' => $this->scoutGroups($groupId)],
            'participants' => ['groups' => $this->participantsByGroup($groupId)],
            'chaperones' => ['groups' => $this->chaperonesByGroup($groupId)],
        };

        $data += [
            'title' => $meta['title'],
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ];

        return $pdf->response($meta['view'], $data, $meta['file'].'-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.pdf', $request->boolean('download'));
    }

    /**
     * @return array{title: string, view: string, file: string}
     */
    private function meta(string $document): array
    {
        return self::DOCUMENTS[$document] ?? abort(404);
    }

    /**
     * Seluruh pangkalan terdaftar beserta jumlah regu, peserta, dan bindamping
     */
    private function scoutGroups(?string $groupId): Collection
    {
        return ScoutGroup::withCount(['patrols', 'participants', 'chaperones'])
            ->when($groupId, fn ($q) => $q->whereKey($groupId))
            ->orderBy('school_name')
            ->get();
    }

    /**
     * Peserta terverifikasi dikelompokkan per gudep (satu gudep tampil pada satu bagian halaman sendiri)
     */
    private function participantsByGroup(?string $groupId): Collection
    {
        $participants = Participant::with('patrol.scoutGroup')
            ->where(function ($q) {
                $q->where('participants.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('patrol.scoutGroup', fn ($sg) => $sg->where('verification_status', RegistrationStatusEnum::Verified));
            })
            ->when($groupId, fn ($q) => $q->whereHas('patrol', fn ($pq) => $pq->where('scout_group_id', $groupId)))
            ->get();

        // Peserta terhubung ke gudep melalui regunya
        $participants->each(fn (Participant $p) => $p->setRelation('scoutGroup', $p->patrol?->scoutGroup));

        return $this->groupedBySchool($participants, fn (Participant $p) => [
            $p->patrol?->gender_category?->value === 'girls' ? 1 : 0,
            $p->patrol?->patrol_name,
            $p->role_in_patrol?->value === 'patrol_leader' ? 0 : ($p->role_in_patrol?->value === 'assistant_patrol_leader' ? 2 : 1),
            $p->full_name,
        ]);
    }

    private function chaperonesByGroup(?string $groupId): Collection
    {
        $chaperones = Chaperone::with(['scoutGroup', 'patrols'])
            ->where(function ($q) {
                $q->where('chaperones.status', RegistrationStatusEnum::Verified)
                    ->orWhereHas('scoutGroup', fn ($sg) => $sg->where('verification_status', RegistrationStatusEnum::Verified));
            })
            ->when($groupId, fn ($q) => $q->where('scout_group_id', $groupId))
            ->get();

        return $this->groupedBySchool($chaperones, fn (Chaperone $c) => [$this->isFemale($c->gender) ? 1 : 0, $c->full_name]);
    }

    /**
     * @param  Collection<int, Participant|Chaperone>  $members
     * @param  callable(Participant|Chaperone): array  $sortKey
     * @return Collection<int, array{group: ScoutGroup, members: Collection}>
     */
    private function groupedBySchool(Collection $members, callable $sortKey): Collection
    {
        return $members
            ->filter(fn ($m) => $m->scoutGroup)
            ->groupBy(fn ($m) => $m->scoutGroup->id)
            ->map(fn (Collection $items) => [
                'group' => $items->first()->scoutGroup,
                'members' => $items->sort(fn ($a, $b) => $sortKey($a) <=> $sortKey($b))->values(),
            ])
            ->sortBy(fn (array $row) => $row['group']->school_name)
            ->values();
    }

    private function isFemale(?string $gender): bool
    {
        return in_array($gender, ['female', 'P', 'girls'], true);
    }
}
