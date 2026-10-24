<?php

namespace Tests\Unit;

use App\Models\CompetitionScore;
use PHPUnit\Framework\TestCase;

class CompetitionScorePredicateTest extends TestCase
{
    public function test_only_ranks_one_to_three_have_achievement_predicate(): void
    {
        foreach (['prestasi_tinggi' => 'Prestasi Tinggi', 'prestasi_baik' => 'Prestasi Baik', 'prestasi_cukup' => 'Prestasi Cukup'] as $predicate => $label) {
            $score = new CompetitionScore(['predicate' => $predicate]);
            $score->predicate = $predicate;

            $this->assertTrue($score->hasAchievement());
            $this->assertSame($label, $score->predicateLabel());
        }

        // Peringkat 4 dan seterusnya tidak mendapat predikat ("partisipasi" bukan predikat tampil)
        $participant = new CompetitionScore;
        $participant->predicate = 'partisipasi';

        $this->assertFalse($participant->hasAchievement());
        $this->assertSame('-', $participant->predicateLabel());
    }
}