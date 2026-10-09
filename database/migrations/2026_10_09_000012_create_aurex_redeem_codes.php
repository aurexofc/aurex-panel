<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aurex_redeem_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 64)->unique();
            $table->unsignedInteger('coins');
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('aurex_redeem_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('claimed_at')->useCurrent();
            $table->unique(['code_id', 'user_id']);

            $table->foreign('code_id')->references('id')->on('aurex_redeem_codes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_redeem_claims');
        Schema::dropIfExists('aurex_redeem_codes');
    }
};
