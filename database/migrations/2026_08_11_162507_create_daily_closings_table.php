<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->unsignedInteger('total_sessions')->default(0);
            $table->timestamp('closed_at');
            $table->foreignId('closed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
