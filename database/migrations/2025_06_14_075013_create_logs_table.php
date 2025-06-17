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
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id', true, true);
            $table->text('description')->nullable();
            $table->string('user')->nullable();
            $table->string('router')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('level')->nullable();
            $table->string('level_name')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('bug_info')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
