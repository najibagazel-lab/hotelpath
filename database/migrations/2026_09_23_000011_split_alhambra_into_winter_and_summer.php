<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'AlHambra Thalasso')->value('id');

        if (! $hotelId) {
            return;
        }

        $yearSeasonId = DB::table('seasons')->where('hotel_id', $hotelId)->where('contract_type', 'YEAR')->value('id');

        // Preserve the existing received status and task progress by converting
        // the former annual period into Winter.
        if ($yearSeasonId) {
            DB::table('seasons')->where('id', $yearSeasonId)->update([
                'contract_type' => 'WINTER',
                'name' => 'Winter 2026/2027 - AlHambra Thalasso',
                'start_date' => '2026-11-01',
                'end_date' => '2027-04-30',
                'updated_at' => now(),
            ]);
        }

        DB::table('seasons')->updateOrInsert(
            ['hotel_id' => $hotelId, 'contract_type' => 'SUMMER'],
            [
                'name' => 'Summer 2027 - AlHambra Thalasso',
                'code' => 'HOTEL-'.$hotelId.'-SUMMER-2027',
                'start_date' => '2027-05-01',
                'end_date' => '2027-10-31',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // The corrected split is the authoritative structure.
    }
};
