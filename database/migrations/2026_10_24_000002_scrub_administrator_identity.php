<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Administrator tidak lagi tercatat atas nama: kosongkan pelaku verifikasi dan identitas Administrator pada
     * penulis pengumuman serta pembuat kode undangan. Log audit tidak diubah.
     */
    public function up(): void
    {
        DB::table('scout_groups')->whereNotNull('verified_by')->update(['verified_by' => null]);

        $adminIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->pluck('role_user.user_id');

        if ($adminIds->isEmpty()) {
            return;
        }

        DB::table('announcements')->whereIn('author_id', $adminIds)->update(['author_id' => null]);
        DB::table('registration_invitations')->whereIn('created_by', $adminIds)->update(['created_by' => null]);
    }

    /**
     * Identitas yang sudah dikosongkan tidak dapat dipulihkan
     */
    public function down(): void
    {
        //
    }
};
