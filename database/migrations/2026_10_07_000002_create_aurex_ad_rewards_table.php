<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aurex rewarded-ads table. Each row is a single ad view attempt:
     * created on "start", marked verified on "complete".
     */
    public function up(): void
    {
        Schema::create('aurex_ad_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 64)->unique();
            $table->unsignedInteger('reward_coins');
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('watched_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'verified']);
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_ad_rewards');
    }
};
