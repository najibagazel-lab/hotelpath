<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('seasons')->where('code', 'W26')->update(['name' => 'Winter 2026/2027']);
        DB::table('seasons')->where('code', 'S27')->update(['name' => 'Summer 2027']);
    }
    public function down(): void
    {
        DB::table('seasons')->where('code', 'W26')->update(['name' => 'Hiver 2026/2027']);
        DB::table('seasons')->where('code', 'S27')->update(['name' => 'Été 2027']);
    }
};
