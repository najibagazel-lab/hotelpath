<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $platforms = DB::table('platforms')->where('active', true)->whereNotNull('platform_group')->get();
        $contractIds = DB::table('contracts')->where('purchase_contract_received', true)->pluck('id');

        foreach ($contractIds as $contractId) {
            foreach ($platforms as $platform) {
                DB::table('contract_tasks')->updateOrInsert(
                    ['contract_id' => $contractId, 'platform_id' => $platform->id],
                    ['assigned_user_id' => DB::table('platform_user')->where('platform_id', $platform->id)->value('user_id'), 'status' => 'PENDING', 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('contract_tasks')->whereIn('platform_id', DB::table('platforms')->whereNotNull('platform_group')->pluck('id'))->delete();
    }
};
