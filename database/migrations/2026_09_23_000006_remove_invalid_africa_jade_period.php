<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'Africa Jade Thalasso')->value('id');

        if (! $hotelId) {
            return;
        }

        $seasonIds = DB::table('seasons')
            ->whereDate('start_date', '2027-11-01')
            ->whereDate('end_date', '2027-12-31')
            ->pluck('id');

        $contractIds = DB::table('contracts')
            ->where('hotel_id', $hotelId)
            ->whereIn('season_id', $seasonIds)
            ->pluck('id');

        // This removes only Africa Jade's invalid two-month contract entry.
        DB::table('contract_tasks')->whereIn('contract_id', $contractIds)->delete();
        DB::table('contracts')->whereIn('id', $contractIds)->delete();
    }

    public function down(): void
    {
        // The invalid entry must not be restored.
    }
};
