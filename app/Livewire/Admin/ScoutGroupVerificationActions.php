<?php

namespace App\Livewire\Admin;

use App\Models\ScoutGroup;
use App\Services\ContingentVerificationService;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Panel keputusan verifikasi kontingen: setujui, minta perbaikan, tolak, dan batalkan (Superadmin).
 * Konfirmasi lewat modal, hasil lewat toast, dan memantau perubahan status dari pengguna lain secara berkala.
 */
#[Lazy]
class ScoutGroupVerificationActions extends Component
{
    public string $scoutGroupId;

    public string $revisionNotes = '';

    public string $rejectNotes = '';

    public string $cancelReason = '';

    /** Sidik status terakhir yang dilihat panel ini; berbeda berarti ada pembaruan dari pihak lain */
    public string $snapshot = '';

    public function mount(string $scoutGroupId): void
    {
        $this->scoutGroupId = $scoutGroupId;
        $this->snapshot = $this->currentSnapshot();
    }

    public function placeholder(): string
    {
        return '<div class="h-40 animate-pulse rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/60"></div>';
    }

    public function verify(ContingentVerificationService $service): void
    {
        $this->decide(function (ScoutGroup $group) use ($service) {
            $accounts = $service->verify($group, auth()->user(), fn (int $percent, string $message) => $this->reportProgress($percent, $message));

            Flux::toast(variant: 'success', heading: 'Kontingen Disetujui', text: "Pendaftaran {$group->school_name} diverifikasi. Akun dibuat: {$accounts['chaperones']} bindamping, {$accounts['participants']} peserta.");
        }, 'verify-modal');
    }

    public function requestRevision(ContingentVerificationService $service): void
    {
        $this->validate(['revisionNotes' => ['required', 'string', 'min:5']], [
            'revisionNotes.required' => 'Catatan perbaikan wajib diisi.',
            'revisionNotes.min' => 'Catatan perbaikan minimal 5 karakter.',
        ]);

        $this->decide(function (ScoutGroup $group) use ($service) {
            $service->requestRevision($group, auth()->user(), $this->revisionNotes);

            Flux::toast(variant: 'warning', heading: 'Perbaikan Diminta', text: "Pendaftaran {$group->school_name} dikembalikan ke Gugus Depan dengan catatan perbaikan.");
        }, 'revision-modal');

        $this->reset('revisionNotes');
    }

    public function reject(ContingentVerificationService $service): void
    {
        $this->validate(['rejectNotes' => ['required', 'string', 'min:5']], [
            'rejectNotes.required' => 'Alasan penolakan wajib diisi.',
            'rejectNotes.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $this->decide(function (ScoutGroup $group) use ($service) {
            $service->reject($group, auth()->user(), $this->rejectNotes);

            Flux::toast(variant: 'danger', heading: 'Pendaftaran Ditolak', text: "Pendaftaran {$group->school_name} telah ditolak.");
        }, 'reject-modal');

        $this->reset('rejectNotes');
    }

    public function cancelVerification(ContingentVerificationService $service): void
    {
        $this->validate(['cancelReason' => ['nullable', 'string', 'max:500']]);

        $this->decide(function (ScoutGroup $group) use ($service) {
            $revoked = $service->cancelVerification($group, auth()->user(), $this->cancelReason);

            Flux::toast(variant: 'warning', heading: 'Verifikasi Dibatalkan', text: "Akun peserta ({$revoked['participants']}) dan bindamping ({$revoked['chaperones']}) dicabut; status kontingen dikembalikan.");
        }, 'cancel-modal');

        $this->reset('cancelReason');
    }

    /**
     * Kirim kemajuan verifikasi ke progress bar di modal selagi permintaan masih berjalan
     */
    private function reportProgress(int $percent, string $message): void
    {
        $this->stream(
            to: 'verify-progress',
            content: view('livewire.admin.partials.verification-progress', compact('percent', 'message'))->render(),
            replace: true,
        );
    }

    /**
     * Dipanggil berkala: beri tahu bila status atau isi kontingen berubah oleh pihak lain
     */
    public function checkForChanges(): void
    {
        $latest = $this->currentSnapshot();

        if ($latest === $this->snapshot) {
            return;
        }

        $this->snapshot = $latest;
        Flux::toast(variant: 'info', heading: 'Data Diperbarui', text: 'Status atau isi pendaftaran kontingen ini baru saja berubah. Tampilan dimuat ulang.');
        $this->dispatch('verification-updated');
    }

    /**
     * Jalankan keputusan, tampilkan galat sebagai toast, lalu segarkan halaman induk
     */
    private function decide(callable $action, string $modal): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $group = ScoutGroup::findOrFail($this->scoutGroupId);

        try {
            $action($group);
        } catch (AuthorizationException|InvalidArgumentException $e) {
            Flux::toast(variant: 'danger', heading: 'Aksi Ditolak', text: $e->getMessage());
        } finally {
            Flux::modal($modal)->close();
        }

        $this->snapshot = $this->currentSnapshot();
        $this->dispatch('verification-updated');
    }

    private function currentSnapshot(): string
    {
        $group = ScoutGroup::withCount(['patrols', 'chaperones', 'participants'])->find($this->scoutGroupId);

        if (! $group) {
            return 'missing';
        }

        return implode('|', [
            $group->verification_status?->value,
            $group->updated_at?->timestamp,
            $group->patrols_count,
            $group->participants_count,
            $group->chaperones_count,
        ]);
    }

    public function render()
    {
        $group = ScoutGroup::findOrFail($this->scoutGroupId);
        $user = auth()->user();

        return view('livewire.admin.scout-group-verification-actions', [
            'group' => $group,
            'isVerified' => $group->verification_status?->value === 'verified',
            'canDecide' => $user->isAdmin() && ! $user->isSuperadmin(),
            'canCancel' => $user->isSuperadmin() && $group->verification_status?->value === 'verified',
        ]);
    }
}
