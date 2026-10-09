<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platforms', function (Blueprint $table) {
            $table->string('platform_group', 20)->nullable()->after('code');
            $table->string('label', 40)->nullable()->after('name');
        });

        $sejourMappings = [
            'DNATA' => 'DNA', 'JUMBO' => 'JMT', 'PEAK' => 'PEAK',
            'SUNHOTELS' => 'SUN', 'SUNQUEST' => 'SQ', 'PAXIMUM' => 'PAXIMUM',
        ];
        foreach ($sejourMappings as $code => $label) {
            DB::table('platforms')->where('code', $code)->update([
                'name' => 'Sejour '.$label,
                'label' => $label,
                'platform_group' => 'SEJOUR',
                'updated_at' => now(),
            ]);
        }

        // Legacy "Sejour" is a former aggregate platform; it is kept for
        // history only and replaced by the explicit sub-platforms below.
        DB::table('platforms')->where('code', 'SEJOUR')->update(['active' => false, 'updated_at' => now()]);

        $newPlatforms = [
            ['Sejour Hey Trip', 'SEJOUR_HEY_TRIP', 'SEJOUR', 'HEY TRIP'],
            ['Sejour Aurum', 'SEJOUR_AURUM', 'SEJOUR', 'AURUM'],
            ['Platform DNA', 'PLATFORM_DNA', 'PLATFORM', 'DNA'],
            ['Platform JMT', 'PLATFORM_JMT', 'PLATFORM', 'JMT'],
            ['Platform Peak', 'PLATFORM_PEAK', 'PLATFORM', 'PEAK'],
            ['Platform Sun', 'PLATFORM_SUN', 'PLATFORM', 'SUN'],
            ['Platform SQ', 'PLATFORM_SQ', 'PLATFORM', 'SQ'],
            ['Platform Hey Trip', 'PLATFORM_HEY_TRIP', 'PLATFORM', 'HEY TRIP'],
        ];

        foreach ($newPlatforms as [$name, $code, $group, $label]) {
            DB::table('platforms')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'label' => $label, 'platform_group' => $group, 'active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('platforms')->whereIn('code', ['SEJOUR_HEY_TRIP', 'SEJOUR_AURUM', 'PLATFORM_DNA', 'PLATFORM_JMT', 'PLATFORM_PEAK', 'PLATFORM_SUN', 'PLATFORM_SQ', 'PLATFORM_HEY_TRIP'])->delete();
        Schema::table('platforms', function (Blueprint $table) {
            $table->dropColumn(['platform_group', 'label']);
        });
    }
};
