<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->whereIn('username', ['najiba', 'amal', 'haifa', 'wafa'])->pluck('id', 'username');
        $assignments = [
            'najiba' => DB::table('platforms')->where('active', true)->where('platform_group', 'SEJOUR')->pluck('id')->all(),
            'amal' => DB::table('platforms')->where('platform_group', 'PLATFORM')->where('label', 'SQ')->pluck('id')->all(),
            'haifa' => DB::table('platforms')->where('platform_group', 'PLATFORM')->whereIn('label', ['SUN', 'DNA', 'JMT'])->pluck('id')->all(),
            'wafa' => DB::table('platforms')->where('platform_group', 'PLATFORM')->whereIn('label', ['PEAK', 'HEY TRIP'])->pluck('id')->all(),
        ];

        DB::table('contract_tasks')->whereIn('assigned_user_id', $users->values())->update(['assigned_user_id' => null]);
        DB::table('platform_user')->whereIn('user_id', $users->values())->delete();

        foreach ($assignments as $username => $platformIds) {
            $userId = $users->get($username);
            foreach ($platformIds as $platformId) {
                DB::table('platform_user')->insertOrIgnore(['platform_id' => $platformId, 'user_id' => $userId]);
            }
            if ($platformIds) {
                DB::table('contract_tasks')->whereIn('platform_id', $platformIds)->update(['assigned_user_id' => $userId]);
            }
        }
    }

    public function down(): void
    {
        // Assignments are operational data and are intentionally not restored automatically.
    }
};
