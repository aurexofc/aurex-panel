<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aurex_premium_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64);
            $table->string('slug', 64)->unique();
            $table->unsignedInteger('duration_days'); // 36500 = lifetime
            $table->unsignedInteger('price_coins');
            $table->unsignedInteger('max_servers')->default(10);
            $table->boolean('ads_free')->default(true);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('aurex_premium_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('package_id');
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // null = lifetime
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('aurex_premium_packages')->onDelete('cascade');
            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_premium_subscriptions');
        Schema::dropIfExists('aurex_premium_packages');
    }
};
