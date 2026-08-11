<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('local_uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parking_lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->string('plate');
            $table->string('plate_normalized');
            $table->timestamp('entry_at');
            $table->timestamp('exit_at')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->string('sync_status')->default('synced');
            $table->timestamps();

            $table->index(['parking_lot_id', 'status', 'plate_normalized']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};
