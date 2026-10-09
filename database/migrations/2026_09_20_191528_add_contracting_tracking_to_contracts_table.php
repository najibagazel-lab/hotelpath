<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('contracting_user_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('contracting_checked_at')->nullable()->after('received_date');
        });

        $now = now();
        $hotels = [
            'Movenpick Resort & Marine Spa', 'Royal Jinene', 'Africa Jade Thalasso', 'AlHambra Thalasso',
            'El Mehdi Beach Resort', 'Eden Yasmine Hotel & Spa Hammamet', 'Delphin Habib Monastir', 'El Mouradi Douz',
            'El Mouradi Hammam Bourguiba', 'El Mouradi Tozeur', 'Golf Residence Sousse', 'Green Golf Hammamet',
            'Hari club Beach', 'Hotel Jinene', 'Mehari Hammamet', 'Cesar Palace', 'Riadh palms',
            'Lella Baya &Thalasso Hammamet', 'Houda Golf Beach & Aquapark', 'Aziza Thalasso Golf',
            'El Mouradi El Menzah', 'Omar Khayam', 'Dar Khayam', 'El Mouradi Port El Kantaoui', 'Rosa Beach',
            'El Mouradi Palace', 'El Mouradi Palm Marina', 'Houda Yasmine Marina & Spa', 'Djerba Aqua Resort',
            'Cesar Thalasso', 'Laico Hammamet', 'Movenpick Hotel Gammarth Tunis', 'Carlton Tunis', 'El Mouradi Skanes',
            'El Mouradi Djerba Menzel', 'Marhaba Club Sousse', 'Meditérranée Thalasso', 'The Russelior Hotel & Spa',
            'Tunisia Lodge', 'Blumar Resort & SPA', 'Kanta Sousse', 'El Mouradi Club Kantaoui', 'El Mouradi Mahdia',
            'El Mouradi Hammamet', 'El Mouradi Cap Mahdia', 'Hammamet Garden Resort & Spa', 'Khayam Garden Beach & Spa',
            'Sousse Pearl Marriott Resort and Spa', 'Jaz Tour Khalef', 'Concorde Paris', 'Barceló Concorde Les Berges du Lac',
            'Sentido Djerba Beach', 'Sentido Phenicia', 'Sahara Beach Aqua Park', 'El Borj', 'Delfino Beach Resort & Spa',
            'VERDI HOTELS ( Ex Ramada Plaza) TUNIS', 'Lac Leman Hotel', 'Royal Tulip Taj Sultan Hammamet',
            'Steigenberger Marhaba Thalasso', 'Business Hotel Sfax', 'Business Hotel Tunis', 'Medina Diar Lemdina & Spa',
            'Medina Solaria & Thalasso', 'Thapsus Beach Resort', 'El Mouradi Club Selima', 'Skanes Seraill', 'Riviera Resort',
            'Nesrine', 'Medina Belisaire Thalasso', 'The Penthouse Suites Hotel', 'Best Beach ( Ex Tergui)', 'Le petit palais',
            'Royal Karthago Resort & Thalasso', 'Telemaque Beach & Spa', 'Golden Tulip President', 'Odyssee Resort Thalassa & Spa',
            'Shalimar', 'Royal Tulip Korbous', 'El Ksar Resort & Thalasso', 'Metropole', 'Kyriad',
        ];

        foreach ($hotels as $name) {
            DB::table('hotels')->insertOrIgnore([
                'name' => $name,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('users')->where('username', 'contracting')->exists()) {
            DB::table('users')->insert([
                'name' => 'Contracting',
                'username' => 'contracting',
                'email' => 'contracting@beslimetravel.local',
                'password' => Hash::make('password'),
                'role' => 'CONTRACTING',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contracting_user_id');
            $table->dropColumn('contracting_checked_at');
        });
    }
};
