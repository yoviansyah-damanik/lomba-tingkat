<?php

namespace App\Livewire\Portal;

use App\Models\Event;
use App\Models\QuestionnaireResponse;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Komponen form kuisioner pengalaman Panitia & Dewan Juri
 */
#[Layout('layouts.app')]
#[Title('Kuisioner Evaluasi Pengalaman — Panitia & Dewan Juri')]
class QuestionnaireForm extends Component
{
    public ?Event $event = null;

    public bool $hasSubmitted = false;

    public ?string $submittedAt = null;

    /** Identitas & Penugasan Responden */
    public string $userRole = 'committee';

    public string $duty_assignment = '';

    /** Rating Bintang (1 - 5) */
    public int $rating_overall = 5;

    public int $rating_ui_ux = 5;

    public int $rating_features = 5;

    public int $rating_speed = 5;

    public int $rating_transparency = 5;

    /** Pertanyaan Pilihan Ganda */
    public string $device_used = 'android';

    public string $efficiency_level = 'very_efficient';

    public string $main_obstacle = 'none';

    public string $most_useful_feature = 'scoring_cockpit';

    public ?string $rubric_clarity = 'very_clear';

    public string $future_readiness = 'mandatory';

    /** Pertanyaan Terbuka & Rekomendasi */
    public string $recommended_features = '';

    public string $experience_feedback = '';

    public string $general_suggestions = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if (! $user->isCommittee() && ! $user->isJudge() && ! $user->isAdmin()) {
            abort(403, 'Kuisioner ini khusus ditujukan untuk Panitia Pelaksana dan Dewan Juri.');
        }

        $this->event = Event::where('is_active', true)->first() ?? Event::first();

        // Tentukan peran responden
        if ($user->isCommitteeAndJudge()) {
            $this->userRole = 'both';
        } elseif ($user->isJudge()) {
            $this->userRole = 'judge';
        } else {
            $this->userRole = 'committee';
        }

        // Tentukan penugasan bawaan
        if ($user->committee_position) {
            $this->duty_assignment = $user->committee_position;
        } elseif ($user->isJudge()) {
            $this->duty_assignment = 'Dewan Juri LT-III';
        } else {
            $this->duty_assignment = 'Panitia Pelaksana';
        }

        // Periksa apakah sudah pernah mengisi kuisioner
        if ($this->event) {
            $existing = QuestionnaireResponse::where('user_id', $user->id)
                ->where('event_id', $this->event->id)
                ->first();

            if ($existing) {
                $this->hasSubmitted = true;
                $this->submittedAt = $existing->updated_at?->translatedFormat('d F Y, H:i') ?? $existing->created_at?->translatedFormat('d F Y, H:i');
                $this->userRole = $existing->role;
                $this->duty_assignment = $existing->duty_assignment ?? $this->duty_assignment;

                $this->rating_overall = (int) $existing->rating_overall;
                $this->rating_ui_ux = (int) $existing->rating_ui_ux;
                $this->rating_features = (int) $existing->rating_features;
                $this->rating_speed = (int) $existing->rating_speed;
                $this->rating_transparency = (int) $existing->rating_transparency;

                $this->device_used = $existing->device_used;
                $this->efficiency_level = $existing->efficiency_level;
                $this->main_obstacle = $existing->main_obstacle;
                $this->most_useful_feature = $existing->most_useful_feature;
                $this->rubric_clarity = $existing->rubric_clarity ?? 'very_clear';
                $this->future_readiness = $existing->future_readiness;

                $this->recommended_features = $existing->recommended_features ?? '';
                $this->experience_feedback = $existing->experience_feedback ?? '';
                $this->general_suggestions = $existing->general_suggestions ?? '';
            }
        }
    }

    /**
     * Set nilai rating secara langsung
     */
    public function setRating(string $field, int $value): void
    {
        $user = auth()->user();
        if (! $user?->isAdmin() && ! $user?->isSuperadmin() && ! ($this->event?->isQuestionnaireOpen() ?? true)) {
            return;
        }

        if (! in_array($field, ['rating_overall', 'rating_ui_ux', 'rating_features', 'rating_speed', 'rating_transparency'], true)) {
            return;
        }

        if ($value >= 1 && $value <= 5) {
            $this->{$field} = $value;
        }
    }

    /**
     * Simpan atau perbarui respon kuisioner
     */
    public function submit(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->event) {
            return;
        }

        if (! $user->isAdmin() && ! $user->isSuperadmin() && ! $this->event->isQuestionnaireOpen()) {
            $msg = $this->event->questionnaireStatus()['message'] ?? 'Pengisian kuisioner saat ini sedang ditutup.';
            Flux::toast(variant: 'danger', heading: 'Kuisioner Ditutup', text: $msg);
            $this->addError('general', $msg);

            return;
        }

        $this->validate([
            'duty_assignment' => ['nullable', 'string', 'max:255'],
            'rating_overall' => ['required', 'integer', 'between:1,5'],
            'rating_ui_ux' => ['required', 'integer', 'between:1,5'],
            'rating_features' => ['required', 'integer', 'between:1,5'],
            'rating_speed' => ['required', 'integer', 'between:1,5'],
            'rating_transparency' => ['required', 'integer', 'between:1,5'],
            'device_used' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::DEVICES))],
            'efficiency_level' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::EFFICIENCY_LEVELS))],
            'main_obstacle' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::OBSTACLES))],
            'most_useful_feature' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::MOST_USEFUL_FEATURES))],
            'rubric_clarity' => ['nullable', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::RUBRIC_CLARITY_OPTIONS))],
            'future_readiness' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionnaireResponse::READINESS_OPTIONS))],
            'recommended_features' => ['nullable', 'string', 'max:3000'],
            'experience_feedback' => ['nullable', 'string', 'max:3000'],
            'general_suggestions' => ['nullable', 'string', 'max:3000'],
        ]);

        $response = QuestionnaireResponse::updateOrCreate(
            [
                'user_id' => $user->id,
                'event_id' => $this->event->id,
            ],
            [
                'role' => $this->userRole,
                'duty_assignment' => $this->duty_assignment,
                'rating_overall' => $this->rating_overall,
                'rating_ui_ux' => $this->rating_ui_ux,
                'rating_features' => $this->rating_features,
                'rating_speed' => $this->rating_speed,
                'rating_transparency' => $this->rating_transparency,
                'device_used' => $this->device_used,
                'efficiency_level' => $this->efficiency_level,
                'main_obstacle' => $this->main_obstacle,
                'most_useful_feature' => $this->most_useful_feature,
                'rubric_clarity' => $this->rubric_clarity,
                'future_readiness' => $this->future_readiness,
                'recommended_features' => trim($this->recommended_features) ?: null,
                'experience_feedback' => trim($this->experience_feedback) ?: null,
                'general_suggestions' => trim($this->general_suggestions) ?: null,
            ]
        );

        $this->hasSubmitted = true;
        $this->submittedAt = now()->translatedFormat('d F Y, H:i');

        Flux::toast(variant: 'success', heading: 'Kuisioner Berhasil Dikirim', text: 'Terima kasih atas penilaian dan masukan berharga Anda untuk evaluasi aplikasi!');
        session()->flash('success_message', 'Kuisioner evaluasi berhasil disimpan. Terima kasih atas partisipasi dan kontribusi Anda!');
    }

    public function render()
    {
        return view('livewire.portal.questionnaire-form', [
            'isOpen' => (bool) ($this->event?->isQuestionnaireOpen() ?? true),
            'status' => $this->event?->questionnaireStatus() ?? ['status' => 'open', 'message' => ''],
            'devices' => QuestionnaireResponse::DEVICES,
            'efficiencyLevels' => QuestionnaireResponse::EFFICIENCY_LEVELS,
            'obstacles' => QuestionnaireResponse::OBSTACLES,
            'mostUsefulFeatures' => QuestionnaireResponse::MOST_USEFUL_FEATURES,
            'rubricClarityOptions' => QuestionnaireResponse::RUBRIC_CLARITY_OPTIONS,
            'readinessOptions' => QuestionnaireResponse::READINESS_OPTIONS,
            'ratingDescriptions' => QuestionnaireResponse::RATING_DESCRIPTIONS,
        ]);
    }
}
