<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'Le petit palais')->value('id');

        if (! $hotelId) {
            return;
        }

        DB::table('seasons')->updateOrInsert(
            ['hotel_id' => $hotelId, 'contract_type' => 'WINTER'],
            [
                'name' => 'Winter 2026/2027 - Le petit palais',
                'code' => 'HOTEL-'.$hotelId.'-WINTER-2026-2027',
                'start_date' => '2026-11-01',
                'end_date' => '2027-03-31',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
{
        // This is an authoritative hotel period.
    }
};
