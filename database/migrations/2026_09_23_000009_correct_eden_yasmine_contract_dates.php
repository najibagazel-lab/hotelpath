<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'Eden Yasmine Hotel & Spa Hammamet')->value('id');

        if (! $hotelId) {
            return;
        }

        DB::table('seasons')->where('hotel_id', $hotelId)->where('contract_type', 'WINTER')->update([
            'end_date' => '2027-04-30',
            'updated_at' => now(),
        ]);
        DB::table('seasons')->where('hotel_id', $hotelId)->where('contract_type', 'SUMMER')->update([
            'start_date' => '2027-05-01',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // The corrected dates are the authoritative values.
    }
};
