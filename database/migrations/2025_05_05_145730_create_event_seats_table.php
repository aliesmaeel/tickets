<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_class_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('row_number')->nullable();
            $table->unsignedInteger('column_number')->nullable();
            $table->string('seat_label')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_reserved')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_seats');
    }
};
