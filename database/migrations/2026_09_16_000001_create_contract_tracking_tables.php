<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('destination')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->restrictOnDelete();
            $table->date('received_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('purchase_contract_received')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['hotel_id', 'season_id']);
        });
        Schema::create('contract_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'BLOCKED'])->default('PENDING');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['contract_id', 'platform_id']);
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('platform_user', function (Blueprint $table) {
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['platform_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('platform_user'); Schema::dropIfExists('activity_logs'); Schema::dropIfExists('contract_tasks'); Schema::dropIfExists('contracts'); Schema::dropIfExists('hotels'); Schema::dropIfExists('seasons'); Schema::dropIfExists('platforms'); }
};
