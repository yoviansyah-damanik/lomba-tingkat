<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom kwarran_name pada tabel scout_groups
     */
    public function up(): void
    {
        if (! Schema::hasColumn('scout_groups', 'kwarran_name')) {
            Schema::table('scout_groups', function (Blueprint $table) {
                $table->string('kwarran_name', 100)->nullable()->after('school_name');
            });
        }

        // Backfill data kwarran dari kolom address untuk data yang sudah ada
        $groups = DB::table('scout_groups')->whereNull('kwarran_name')->get();
        $knownKwarrans = [
            'Padangsidimpuan Utara',
            'Padangsidimpuan Selatan',
            'Padangsidimpuan Batunadua',
            'Padangsidimpuan Hutaimbaru',
            'Padangsidimpuan Tenggara',
            'Padangsidimpuan Angkola Julu',
        ];

        foreach ($groups as $group) {
            $matched = null;
            foreach ($knownKwarrans as $k) {
                if (stripos($group->address ?? '', $k) !== false) {
                    $matched = $k;
                    break;
                }
            }

            if ($matched) {
                DB::table('scout_groups')
                    ->where('id', $group->id)
                    ->update(['kwarran_name' => $matched]);
            }
        }
    }

    /**
     * Rollback migrasi
     */
    public function down(): void
    {
        if (Schema::hasColumn('scout_groups', 'kwarran_name')) {
            Schema::table('scout_groups', function (Blueprint $table) {
                $table->dropColumn('kwarran_name');
            });
        }
    }
};
