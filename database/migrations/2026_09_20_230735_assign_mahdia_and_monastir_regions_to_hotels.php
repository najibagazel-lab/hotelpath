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
        DB::table('hotels')->whereIn('name', [
            'El Mehdi Beach Resort', 'Thapsus Beach Resort',
        ])->update(['destination' => 'Mahdia']);

        DB::table('hotels')->whereIn('name', [
            'Delphin Habib Monastir', 'Houda Golf Beach & Aquapark', 'Rosa Beach', 'Skanes Seraill',
        ])->update(['destination' => 'Monastir']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hotels')->whereIn('destination', ['Mahdia', 'Monastir'])->update(['destination' => null]);
    }
};
