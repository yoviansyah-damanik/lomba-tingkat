<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentCriterion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assessment_criteria';

    protected $fillable = [
        'competition_id',
        'name',
        'code',
        'description',
        'min_score',
        'max_score',
        'weight_percentage',
        'order_index',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight_percentage' => 'decimal:2',
        'order_index' => 'integer',
    ];

    /**
     * Relasi ke cabang lomba
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
