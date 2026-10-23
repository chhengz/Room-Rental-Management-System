<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('meter_code')->unique();
            $table->date('installed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('utility_rates', function (Blueprint $table) {
            $table->id();
            $table->string('utility_type');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('unit_rate', 12, 4);
            $table->decimal('fixed_charge', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['utility_type', 'effective_from', 'effective_to']);
        });

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_meter_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('previous_reading', 12, 2);
            $table->decimal('current_reading', 12, 2);
            $table->decimal('consumption', 12, 2);
            $table->decimal('unit_rate', 12, 4);
            $table->decimal('fixed_charge', 12, 2)->default(0);
            $table->decimal('charge_amount', 12, 2);
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['utility_meter_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('utility_rates');
        Schema::dropIfExists('utility_meters');
    }
};
