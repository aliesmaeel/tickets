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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['party', 'football', 'concert', 'conference']);
            $table->string('image')->nullable();
            $table->text('address')->nullable();
            $table->string('address_link',250)->nullable();
            $table->string('address_image')->nullable();
            $table->string('start_time');
            $table->string('end_time');
            $table->string('display_start_date')->nullable();
            $table->string('display_end_date')->nullable();

            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();

            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('time_to_place_cache_order')->default(0);
            $table->unsignedBigInteger('max_cache_orders')->default(0);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
