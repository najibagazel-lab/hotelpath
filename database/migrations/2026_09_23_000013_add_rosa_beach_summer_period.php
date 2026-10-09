<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'Rosa Beach')->value('id');

        if (! $hotelId) {
            return;
        }

        DB::table('seasons')->updateOrInsert(
            ['hotel_id' => $hotelId, 'contract_type' => 'SUMMER'],
            [
                'name' => 'Summer 2027 - Rosa Beach',
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
        // This is a real hotel period from the Excel data.
    }
};
