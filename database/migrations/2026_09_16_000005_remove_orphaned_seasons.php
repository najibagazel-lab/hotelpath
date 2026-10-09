<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('seasons')->whereNotExists(function ($query) {
            $query->selectRaw(1)->from('contracts')->whereColumn('contracts.season_id', 'seasons.id');
        })->delete();
    }
    public function down(): void {}
};
