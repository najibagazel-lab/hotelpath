<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Exact hotel names imported from the last Excel file.
        $excelHotels = [
            'Africa Jade Thalasso', 'AlHambra Thalasso', 'Ariha', 'Aziza Thalasso Golf', 'Best Beach ( Ex Tergui)',
            'Blue Beach golf and Spa', 'Blumar Resort & SPA', 'Business Hotel Sfax', 'Business Hotel Tunis', 'Calimera Delfino Beach Resort',
            'Carlton Tunis', 'Cesar Palace', 'Cesar Thalasso', 'Concorde Green Park', 'Concorde Les Berges du Lac', 'Concorde Marco polo',
            'Concorde Paris', 'Dar Khayam', 'Delphin Habib Monastir', 'Djerba Aqua Resort', 'Eden Yasmine Hotel & Spa Hammamet', 'El Borj',
            'El Ksar Resort & Thalasso', 'El Mehdi Beach Resort', 'El Mouradi Hammamet', 'Golden Tulip President', 'Golf Residence Sousse',
            'Green Golf Hammamet', 'Hammamet Garden', 'Hari club Beach', 'Hotel Jinene', 'Houda Golf Beach & Aquapark',
            'Houda Yasmine Marina & Spa', 'Kanta Sousse', 'Khayam Garden', 'Kyriad', 'Lac Leman Hotel', 'Laico Hammamet',
            'Le petit palais', 'Lella Baya Thalasso', 'Marhaba Club Sousse', 'Medina Belisaire', 'Medina Diar Lemdina',
            'Medina Solaria & Thalasso', 'Mediterranee Thalasso', 'Mehari Hammamet', 'Metropole', 'Movenpick Hotel Gammarth Tunis',
            'Movenpick Resort & Marine Spa', 'Nahrawess Resort & Thalasso', 'Nesrine', 'Odyssee Resort Thalassa & Spa', 'Omar Khayam',
            'Riadh palms', 'Riviera Resort', 'Rosa Beach', 'Royal Jinene', 'Royal Karthago', 'Royal Tulip Korbous',
            'Royal Tulip Taj Sultan Hammamet', 'Sahara Beach Aqua Park', 'Sentido Djerba Beach', 'Sentido Phenicia', 'Shalimar',
            'Skanes Seraill', 'Sousse Pearl Marriott Resort and Spa', 'Steigenberger Marhaba Thalasso', 'Telemaque Beach & Spa',
            'Thapsus Beach Resort', 'The Penthouse Suites Hotel', 'The Russelior Hotel & Spa', 'Tour Khalef', 'Tunisia Lodge',
            'VERDI HOTELS ( Ex Ramada Plaza) TUNIS', 'Zodiac',
        ];

        $oldHotelIds = DB::table('hotels')->whereNotIn('name', $excelHotels)->pluck('id');
        $oldContractIds = DB::table('contracts')->whereIn('hotel_id', $oldHotelIds)->pluck('id');
        $legacyContractIds = DB::table('contracts')
            ->leftJoin('seasons', 'contracts.season_id', '=', 'seasons.id')
            ->where(function ($query) {
                $query->whereNull('seasons.hotel_id')->orWhereNull('seasons.contract_type');
            })
            ->pluck('contracts.id');
        $contractIds = $oldContractIds->merge($legacyContractIds)->unique();

        DB::table('notifications')->orderBy('created_at')->get()->each(function ($notification) use ($contractIds) {
            $data = json_decode($notification->data, true) ?: [];
            if (in_array((int) ($data['contract_id'] ?? 0), $contractIds->all(), true)) {
                DB::table('notifications')->where('id', $notification->id)->delete();
            }
        });

        DB::table('activity_logs')->whereIn('contract_id', $contractIds)->update(['contract_id' => null]);
        DB::table('contract_tasks')->whereIn('contract_id', $contractIds)->delete();
        DB::table('contracts')->whereIn('id', $contractIds)->delete();
        DB::table('hotels')->whereIn('id', $oldHotelIds)->delete();
    }

    public function down(): void
    {
        // The removed legacy data must not be restored.
    }
};
