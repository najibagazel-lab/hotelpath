<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $sunhotels = DB::table('platforms')->where('code', 'SUNHOTELS')->first();
        if (! $sunhotels) {
            DB::table('platforms')->insert(['name' => 'Sunhotels', 'code' => 'SUNHOTELS', 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        $najiba = DB::table('users')->where('username', 'najiba')->first();
        $sejour = DB::table('platforms')->where('code', 'SEJOUR')->first();
        if ($najiba && $sejour && ! DB::table('platform_user')->where(['platform_id' => $sejour->id, 'user_id' => $najiba->id])->exists()) {
            DB::table('platform_user')->insert(['platform_id' => $sejour->id, 'user_id' => $najiba->id]);
        }
    }
    public function down(): void {}
};
