<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotels')->where('name', 'Khayam Garden')->update([
            'destination' => 'Nabeul',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Nabeul is the requested region.
    }
};
