<?php

namespace App\Livewire\Portal;

use App\Services\MyAttendanceService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Daftar jadwal presensi pengguna, dikelompokkan per tanggal dengan filter berlangsung/riwayat
 */
#[Lazy]
class MyAttendanceList extends Component
{
    public string $filter = 'upcoming'; // upcoming, history, all

    /** Tab untuk juri: duties (tugas penjurian) atau presence (presensi pribadi); null = default menurut peran */
    public ?string $tab = null;

    /**
     * Juri langsung melihat jadwal penunjukannya; peran lain langsung melihat presensi pribadi
     */
    #[Computed]
    public function activeTab(): string
    {
        return $this->tab ?? (auth()->user()->isJudge() ? 'duties' : 'presence');
    }

    public function placeholder(): string
    {
        return '<div class="h-64 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    #[Computed]
    public function items(): Collection
    {
        return app(MyAttendanceService::class)->items(auth()->user());
    }

    #[Computed]
    public function summary(): array
    {
        $items = $this->items;

        return [
            'present' => $items->where('status', MyAttendanceService::STATUS_PRESENT)->count(),
            'open' => $items->where('status', MyAttendanceService::STATUS_OPEN)->count(),
            'upcoming' => $items->where('status', MyAttendanceService::STATUS_UPCOMING)->count(),
            'absent' => $items->where('status', MyAttendanceService::STATUS_ABSENT)->count(),
        ];
    }

    #[Computed]
    public function duties(): Collection
    {
        return app(MyAttendanceService::class)->judgeDuties(auth()->user());
    }

    /**
     * Item terfilter: "upcoming" = belum berakhir, "history" = sudah lewat atau sudah hadir
     */
    #[Computed]
    public function grouped(): Collection
    {
        $filtered = $this->items->filter(function (array $item) {
            $finished = $item['window'] === MyAttendanceService::STATUS_ABSENT;

            return match ($this->filter) {
                'upcoming' => ! $finished,
                'history' => $finished,
                default => true,
            };
        });

        return $filtered->groupBy(fn (array $item) => $item['start']?->timezone('Asia/Jakarta')->toDateString() ?? 'Tanpa tanggal');
    }

    public function render()
    {
        return view('livewire.portal.my-attendance-list');
    }
}
