<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotels')
            ->where('name', 'test')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('contracts')->whereColumn('contracts.hotel_id', 'hotels.id');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('seasons')->whereColumn('seasons.hotel_id', 'hotels.id');
            })
            ->delete();
    }

    public function down(): void
    {
        // This was an empty test record created during validation testing.
    }
};
