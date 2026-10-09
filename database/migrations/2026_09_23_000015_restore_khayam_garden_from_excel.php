<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $hotelId = DB::table('hotels')->where('name', 'Khayam Garden')->value('id');

        if (! $hotelId) {
            $hotelId = DB::table('hotels')->insertGetId([
                'name' => 'Khayam Garden',
                'destination' => 'Nabeul',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('hotels')->where('id', $hotelId)->update(['destination' => 'Nabeul', 'active' => true, 'updated_at' => $now]);
        }

        DB::table('seasons')->updateOrInsert(
            ['hotel_id' => $hotelId, 'contract_type' => 'YEAR'],
            [
                'name' => 'Year 2026/2027 - Khayam Garden',
                'code' => 'HOTEL-'.$hotelId.'-YEAR-2026-2027',
                'start_date' => '2026-11-01',
                'end_date' => '2027-10-31',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // Restored Excel data is retained.
    }
};
