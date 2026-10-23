<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('total_floors')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('default_capacity')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $table->string('room_number');
            $table->unsignedSmallInteger('floor');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('rental_rate', 12, 2);
            $table->string('status')->default('available');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->unique(['building_id', 'room_number']);
            $table->index(['building_id', 'floor', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('buildings');
    }
};
