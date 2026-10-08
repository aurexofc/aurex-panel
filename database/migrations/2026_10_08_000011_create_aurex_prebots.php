<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aurex_prebots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('github_url');
            $table->string('icon')->default('🤖');
            $table->unsignedInteger('price_coins')->default(0);
            $table->unsignedInteger('memory')->default(512);
            $table->unsignedInteger('disk')->default(2048);
            $table->unsignedInteger('cpu')->default(100);
            $table->unsignedInteger('egg_id')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_prebots');
    }
};
