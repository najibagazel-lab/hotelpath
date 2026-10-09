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
        DB::table('hotels')->where('name', 'Khayam Garden Beach & Spa')->update(['destination' => 'Nabeul']);

        $now = now();
        foreach (['Golden Yasmine Ras El Ain'] as $name) {
            if (! DB::table('hotels')->where('name', $name)->exists()) {
                DB::table('hotels')->insert(['name' => $name, 'destination' => 'Tozeur', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            } else {
                DB::table('hotels')->where('name', $name)->update(['destination' => 'Tozeur']);
            }
        }

        $tunisHotels = [
            'Business Hotel Tunis', 'Business Hotel Sfax', 'Carlton Tunis', 'Barceló Concorde Les Berges du Lac',
            'Concorde Paris', 'Kyriad', 'Lac Leman Hotel', 'Movenpick Hotel Gammarth Tunis',
            'The Penthouse Suites Hotel', 'VERDI HOTELS ( Ex Ramada Plaza) TUNIS', 'Golden Tulip Mechtel',
            'La Maison Blanche', 'Ariba',
        ];
        foreach ($tunisHotels as $name) {
            if (! DB::table('hotels')->where('name', $name)->exists()) {
                DB::table('hotels')->insert(['name' => $name, 'destination' => 'Tunis', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
            } else {
                DB::table('hotels')->where('name', $name)->update(['destination' => 'Tunis']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hotels')->whereIn('destination', ['Nabeul', 'Tozeur', 'Tunis'])->update(['destination' => null]);
    }
};
