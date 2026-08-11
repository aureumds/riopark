<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariff_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_per_hour', 10, 2)->default(0);
            $table->unsignedInteger('grace_minutes')->default(0);
            $table->unsignedInteger('fraction_minutes')->default(30);
            $table->decimal('fraction_price', 10, 2)->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_rules');
    }
};
