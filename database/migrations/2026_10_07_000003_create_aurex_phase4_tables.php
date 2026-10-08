<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aurex phase 4 tables: referrals, announcements, support tickets.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('aurex_referral_code', 16)->nullable()->unique()->after('remember_token');
        });

        Schema::create('aurex_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('referrer_id');
            $table->unsignedInteger('referred_id')->unique();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('aurex_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aurex_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('subject');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });

        Schema::create('aurex_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedInteger('user_id');
            $table->boolean('is_staff')->default(false);
            $table->text('message');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('aurex_tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_ticket_messages');
        Schema::dropIfExists('aurex_tickets');
        Schema::dropIfExists('aurex_announcements');
        Schema::dropIfExists('aurex_referrals');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('aurex_referral_code');
        });
    }
};
