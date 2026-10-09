<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $longNameId = DB::table('hotels')->where('name', 'Khayam Garden Beach & Spa')->value('id');
        $shortNameId = DB::table('hotels')->where('name', 'Khayam Garden')->value('id');

        if ($longNameId && ! $shortNameId) {
            DB::table('hotels')->where('id', $longNameId)->update([
                'name' => 'Khayam Garden',
                'destination' => 'Nabeul',
                'updated_at' => now(),
            ]);
            DB::table('seasons')->where('hotel_id', $longNameId)->update([
                'name' => 'Year 2026/2027 - Khayam Garden',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // The short name is the requested authoritative name.
    }
};
