<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Regu lama (satu bindamping per kategori) otomatis didampingi bindamping sekategori di gudepnya bila tepat satu
     */
    public function up(): void
    {
        foreach (DB::table('patrols')->whereNull('chaperone_id')->get(['id', 'scout_group_id', 'gender_category']) as $patrol) {
            $gender = $patrol->gender_category === 'girls' ? 'female' : 'male';

            $chaperoneIds = DB::table('chaperones')
                ->where('scout_group_id', $patrol->scout_group_id)
                ->where('gender', $gender)
                ->pluck('id');

            if ($chaperoneIds->count() === 1) {
                DB::table('patrols')->where('id', $patrol->id)->update(['chaperone_id' => $chaperoneIds->first()]);
            }
        }
    }

    /**
     * Penetapan bindamping tidak dapat dibedakan dari data hasil input pengguna, sehingga tidak dibatalkan
     */
    public function down(): void
    {
        //
    }
};
