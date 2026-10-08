<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aurex billing tables: server plans sold for coins + the coins ledger.
     */
    public function up(): void
    {
        Schema::create('aurex_server_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('memory'); // MB
            $table->unsignedInteger('cpu'); // percent
            $table->unsignedInteger('disk'); // MB
            $table->unsignedInteger('price_coins');
            $table->unsignedInteger('duration_days')->default(30);
            $table->unsignedBigInteger('egg_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('aurex_coins_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->integer('amount'); // positive = earned, negative = spent
            $table->string('reason');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_coins_ledger');
        Schema::dropIfExists('aurex_server_plans');
    }
};
