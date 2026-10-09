<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $platform = DB::table('platforms')->where('code', 'SUNHOTELS')->first();
        if (! $platform) return;
        foreach (DB::table('contracts')->pluck('id') as $contractId) {
            if (! DB::table('contract_tasks')->where(['contract_id' => $contractId, 'platform_id' => $platform->id])->exists()) {
                DB::table('contract_tasks')->insert(['contract_id' => $contractId, 'platform_id' => $platform->id, 'status' => 'PENDING', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
    public function down(): void {}
};
