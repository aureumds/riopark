<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('active');
            }
            if (! Schema::hasColumn('companies', 'paid_until')) {
                $table->date('paid_until')->nullable()->after('subscription_status');
            }
        });

        if (! Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parking_lot_id')->constrained()->cascadeOnDelete();
                $table->string('device_uid')->unique();
                $table->string('label')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('licenses')) {
            Schema::create('licenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('device_id')->constrained()->cascadeOnDelete();
                $table->uuid('jti')->unique();
                $table->text('token');
                $table->dateTime('issued_at');
                $table->dateTime('expires_at');
                $table->dateTime('revoked_at')->nullable();
                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('devices');

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'paid_until')) {
                $table->dropColumn('paid_until');
            }
            if (Schema::hasColumn('companies', 'subscription_status')) {
                $table->dropColumn('subscription_status');
            }
        });
    }
};
