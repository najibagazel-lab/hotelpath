<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->string('contract_type', 10)->nullable()->after('hotel_id');
        });

        /*
         * Source: "Feuille de calcul sans titre (4).xlsx".
         * These records deliberately contain the dates supplied for each hotel;
         * Winter/Summer dates are never derived from one another.
         */
        $periods = [
            ['Djerba', 'Odyssee Resort Thalassa & Spa', 'YEAR', '2027-02-27', '2027-11-30'],
            ['Djerba', 'Le petit palais', 'WINTER', '2026-11-01', '2027-03-31'],
            ['Djerba', 'Le petit palais', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Djerba', 'Hari club Beach', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Djerba', 'Hari club Beach', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Djerba', 'Djerba Aqua Resort', 'WINTER', '2026-11-01', '2027-03-31'],
            ['Djerba', 'Djerba Aqua Resort', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Djerba', 'Cesar Thalasso', 'WINTER', '2026-11-01', '2027-03-31'],
            ['Djerba', 'Cesar Thalasso', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Djerba', 'Royal Karthago', 'WINTER', '2026-11-01', '2027-03-31'],
            ['Djerba', 'Royal Karthago', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Djerba', 'Telemaque Beach & Spa', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'AlHambra Thalasso', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'AlHambra Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Aziza Thalasso Golf', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Dar Khayam', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Hammamet Garden', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Golden Tulip President', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Green Golf Hammamet', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Blumar Resort & SPA', 'SUMMER', '2027-04-01', '2027-11-01'],
            ['Hammamet', 'Concorde Marco polo', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Concorde Marco polo', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Eden Yasmine Hotel & Spa Hammamet', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Eden Yasmine Hotel & Spa Hammamet', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'El Mouradi Hammamet', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Houda Yasmine Marina & Spa', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Houda Yasmine Marina & Spa', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Laico Hammamet', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Laico Hammamet', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Lella Baya Thalasso', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Lella Baya Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Steigenberger Marhaba Thalasso', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Steigenberger Marhaba Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Medina Belisaire', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Medina Diar Lemdina', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Medina Solaria & Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Mediterranee Thalasso', 'SUMMER', '2027-04-11', '2027-10-31'],
            ['Hammamet', 'Mehari Hammamet', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Shalimar', 'SUMMER', '2027-04-01', '2027-11-06'],
            ['Hammamet', 'Nahrawess Resort & Thalasso', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Hammamet', 'Nahrawess Resort & Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Hammamet', 'Nesrine', 'YEAR', '2026-11-16', '2027-11-15'],
            ['Hammamet', 'Omar Khayam', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Royal Tulip Taj Sultan Hammamet', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'The Russelior Hotel & Spa', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Tunisia Lodge', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Hammamet', 'Zodiac', 'YEAR', '2026-11-02', '2027-11-02'],
            ['Korba', 'Africa Jade Thalasso', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Korbous', 'Royal Tulip Korbous', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Mahdia', 'El Mehdi Beach Resort', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Mahdia', 'El Mehdi Beach Resort', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Mahdia', 'Thapsus Beach Resort', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Monastir', 'Blue Beach golf and Spa', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Monastir', 'Houda Golf Beach & Aquapark', 'SUMMER', '2027-04-24', '2027-10-31'],
            ['Monastir', 'Rosa Beach', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Monastir', 'Rosa Beach', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Monastir', 'Delphin Habib Monastir', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Monastir', 'Skanes Seraill', 'YEAR', '2026-11-13', '2027-10-31'],
            ['Nabeul', 'Khayam Garden', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Golf Residence Sousse', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Hotel Jinene', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Sousse', 'Cesar Palace', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Riadh palms', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'Riadh palms', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Royal Jinene', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Movenpick Resort & Marine Spa', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Marhaba Club Sousse', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'Marhaba Club Sousse', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Sousse Pearl Marriott Resort and Spa', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'Sousse Pearl Marriott Resort and Spa', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Tour Khalef', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'Tour Khalef', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Best Beach ( Ex Tergui)', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Riviera Resort', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Sousse', 'Concorde Green Park', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'Concorde Green Park', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'El Ksar Resort & Thalasso', 'WINTER', '2026-11-01', '2027-04-30'],
            ['Sousse', 'El Ksar Resort & Thalasso', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['Sousse', 'Kanta Sousse', 'WINTER', '2026-11-01', '2027-03-31'],
            ['Sousse', 'Kanta Sousse', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Tunis', 'Ariha', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Movenpick Hotel Gammarth Tunis', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Carlton Tunis', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Concorde Paris', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Concorde Les Berges du Lac', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'VERDI HOTELS ( Ex Ramada Plaza) TUNIS', 'YEAR', '2026-11-01', '2027-10-31'],
            ['Tunis', 'Lac Leman Hotel', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Business Hotel Tunis', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Metropole', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'Kyriad', 'YEAR', '2027-01-01', '2027-12-31'],
            ['Tunis', 'The Penthouse Suites Hotel', 'YEAR', '2027-01-01', '2027-12-31'],
            ['TTS', 'Sentido Djerba Beach', 'SUMMER', '2027-05-01', '2027-10-31'],
            ['TTS', 'Sentido Phenicia', 'YEAR', '2026-11-01', '2027-10-31'],
            ['TTS', 'Sahara Beach Aqua Park', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['TTS', 'El Borj', 'SUMMER', '2027-06-01', '2027-10-31'],
            ['TTS', 'Calimera Delfino Beach Resort', 'SUMMER', '2027-04-01', '2027-10-31'],
            ['Sfax', 'Business Hotel Sfax', 'YEAR', '2027-01-01', '2027-12-31'],
        ];

        foreach ($periods as [$region, $hotelName, $type, $startDate, $endDate]) {
            $hotelId = DB::table('hotels')->where('name', $hotelName)->value('id');

            if (! $hotelId) {
                $hotelId = DB::table('hotels')->insertGetId([
                    'name' => $hotelName,
                    'destination' => $region,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('hotels')->where('id', $hotelId)->update(['destination' => $region, 'active' => true, 'updated_at' => now()]);
            }

            $name = sprintf('%s %s/%s - %s', ucfirst(strtolower($type)), substr($startDate, 0, 4), substr($endDate, 0, 4), $hotelName);
            DB::table('seasons')->updateOrInsert(
                ['hotel_id' => $hotelId, 'contract_type' => $type, 'start_date' => $startDate, 'end_date' => $endDate],
                ['name' => $name, 'code' => 'EXCEL-'.$hotelId.'-'.$type.'-'.str_replace('-', '', $startDate), 'active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('seasons')->whereNotNull('hotel_id')->whereNotNull('contract_type')->delete();

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('contract_type');
        });
    }
};
