<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $sejourPlatforms = [
            ['Sejour DNA', 'DNATA', 'DNA'],
            ['Sejour Peak', 'PEAK', 'PEAK'],
            ['Sejour Sun', 'SUNHOTELS', 'SUN'],
            ['Sejour JMT', 'JUMBO', 'JMT'],
            ['Sejour Hey Trip', 'SEJOUR_HEY_TRIP', 'HEY TRIP'],
            ['Sejour Sunquest', 'SUNQUEST', 'SUNQUEST'],
            ['Sejour Aurum', 'SEJOUR_AURUM', 'AURUM'],
            ['Sejour Paximum', 'PAXIMUM', 'PAXIMUM'],
        ];

        $platforms = [
            ['Platform DNA', 'PLATFORM_DNA', 'DNA'],
            ['Platform Peak', 'PLATFORM_PEAK', 'PEAK'],
            ['Platform Sun', 'PLATFORM_SUN', 'SUN'],
            ['Platform Sunquest', 'PLATFORM_SQ', 'SUNQUEST'],
            ['Platform JMT', 'PLATFORM_JMT', 'JMT'],
            ['Platform Emerging', 'PLATFORM_EMERGING', 'EMERGING'],
            ['Platform Hey Trip', 'PLATFORM_HEY_TRIP', 'HEY TRIP'],
        ];

        foreach ($sejourPlatforms as [$name, $code, $label]) {
            DB::table('platforms')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'label' => $label, 'platform_group' => 'SEJOUR', 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach ($platforms as [$name, $code, $label]) {
            DB::table('platforms')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'label' => $label, 'platform_group' => 'PLATFORM', 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $allPlatformIds = DB::table('platforms')
            ->where('active', true)
            ->whereIn('platform_group', ['SEJOUR', 'PLATFORM'])
            ->pluck('id');

        DB::table('contracts')->where('purchase_contract_received', true)->pluck('id')->each(function ($contractId) use ($allPlatformIds, $now) {
            foreach ($allPlatformIds as $platformId) {
                DB::table('contract_tasks')->insertOrIgnore([
                    'contract_id' => $contractId,
                    'platform_id' => $platformId,
                    'assigned_user_id' => null,
                    'is_active' => true,
                    'status' => 'PENDING',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // This migration aligns operational platform labels and is not reversed automatically.
    }
};
