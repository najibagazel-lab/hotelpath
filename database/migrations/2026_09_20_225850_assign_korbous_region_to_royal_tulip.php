<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('hotels')->where('name', 'Royal Tulip Korbous')->update(['destination' => 'Korbous']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hotels')->where('name', 'Royal Tulip Korbous')->update(['destination' => null]);
    }
};
