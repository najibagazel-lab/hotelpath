<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platforms')
            ->where('code', 'PLATFORM_EMERGING')
            ->update([
                'name' => 'Platform BBX Online',
                'label' => 'BBX ONLINE',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('platforms')
            ->where('code', 'PLATFORM_EMERGING')
            ->update([
                'name' => 'Platform Emerging',
                'label' => 'EMERGING',
                'updated_at' => now(),
            ]);
    }
};
