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
            'Best Beach ( Ex Tergui)', 'Cesar Palace', 'El Ksar', 'El Ksar Resort & Thalasso',
            'Golf Residence Sousse', 'Golf Résidence', 'Hotel Jinene', 'Kanta Sousse',
            'Marhaba Club Sousse', 'Movenpick Resort & Marine Spa', 'Riadh Palms', 'Riadh palms',
            'Riviera Resort', 'Royal Jinene', 'Sousse Pearl Marriott Resort and Spa', 'Jaz Tour Khalef',
        ])->update(['destination' => 'Sousse']);

        DB::table('hotels')->whereIn('name', [
            'AlHambra Thalasso', 'Aziza Thalasso Golf', 'Blumar Resort & SPA', 'Dar Khayam',
            'Eden Yasmine Hotel & Spa Hammamet', 'El Mouradi Hammamet', 'Golden Tulip President',
            'Green Golf Hammamet', 'Hammamet Garden Resort & Spa', 'Houda Yasmine Marina & Spa',
            'Laico Hammamet', 'Lella Baya &Thalasso Hammamet', 'Medina Belisaire Thalasso',
            'Medina Diar Lemdina & Spa', 'Medina Solaria & Thalasso', 'Meditérranée Thalasso',
            'Mehari Hammamet', 'Nesrine', 'Omar Khayam', 'Royal Tulip Taj Sultan Hammamet',
            'The Russelior Hotel & Spa', 'Shalimar', 'Steigenberger Marhaba Thalasso', 'Tunisia Lodge',
        ])->update(['destination' => 'Hammamet']);

        DB::table('hotels')->whereIn('name', [
            'Cesar Thalasso', 'Djerba Aqua Resort', 'Hari club Beach', 'Le petit palais',
            'Odyssee Resort Thalassa & Spa', 'Royal Karthago Resort & Thalasso', 'Telemaque Beach & Spa',
        ])->update(['destination' => 'Djerba']);

        DB::table('hotels')->where('name', 'Africa Jade Thalasso')->update(['destination' => 'Korba']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hotels')->whereIn('destination', ['Sousse', 'Hammamet', 'Djerba', 'Korba'])->update(['destination' => null]);
    }
};
