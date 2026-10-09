<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('platforms')->updateOrInsert(
            ['code' => 'PLATFORM_EMERGING'],
            [
                'name' => 'Platform Emerging',
                'label' => 'EMERGING',
                'platform_group' => 'PLATFORM',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $platformId = DB::table('platforms')->where('code', 'PLATFORM_EMERGING')->value('id');

        // Existing received contracts must also receive the new task so that
        // assigning Emerging to Ameni immediately unlocks the checkboxes.
        DB::table('contracts')->where('purchase_contract_received', true)->pluck('id')->each(function ($contractId) use ($platformId, $now) {
            DB::table('contract_tasks')->updateOrInsert(
                ['contract_id' => $contractId, 'platform_id' => $platformId],
                ['assigned_user_id' => null, 'status' => 'PENDING', 'updated_at' => $now, 'created_at' => $now]
            );
        });
    }

    public function down(): void
    {
        $platformId = DB::table('platforms')->where('code', 'PLATFORM_EMERGING')->value('id');
        DB::table('contract_tasks')->where('platform_id', $platformId)->delete();
        DB::table('platforms')->where('id', $platformId)->delete();
    }
};
