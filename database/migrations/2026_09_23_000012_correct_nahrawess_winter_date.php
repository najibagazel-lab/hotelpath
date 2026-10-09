<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hotelId = DB::table('hotels')->where('name', 'Nahrawess Resort & Thalasso')->value('id');

        if ($hotelId) {
            DB::table('seasons')->where('hotel_id', $hotelId)->where('contract_type', 'WINTER')->update([
                'end_date' => '2027-04-30',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // The corrected date is authoritative.
    }
};
