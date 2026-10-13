<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('platforms')->updateOrInsert(
            ['code' => 'PLATFORM_HEY_TRIP'],
            [
                'name' => 'Platform Hey Trip',
                'label' => 'HEY TRIP',
                'platform_group' => 'PLATFORM',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $platformId = DB::table('platforms')->where('code', 'PLATFORM_HEY_TRIP')->value('id');

        if (! $platformId) {
            return;
        }

        DB::table('contract_tasks')->where('platform_id', $platformId)->update([
            'is_active' => true,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->where('purchase_contract_received', true)->pluck('id')->each(function ($contractId) use ($platformId, $now) {
            DB::table('contract_tasks')->insertOrIgnore([
                'contract_id' => $contractId,
                'platform_id' => $platformId,
                'assigned_user_id' => null,
                'is_active' => true,
                'status' => 'PENDING',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        // Platform membership is operational data and is kept on rollback.
    }
};
