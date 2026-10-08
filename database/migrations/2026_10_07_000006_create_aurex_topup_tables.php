<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aurex manual coin top-ups (Easypaisa/JazzCash/USDT/Binance).
     * Users buy a package, pay manually, submit the transaction reference;
     * admin verifies and approves, coins are credited.
     */
    public function up(): void
    {
        Schema::create('aurex_topup_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('coins');
            $table->unsignedInteger('price_pkr');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('aurex_topup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedInteger('coins');
            $table->unsignedInteger('price_pkr');
            $table->string('method', 30);
            $table->string('transaction_ref', 100);
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('aurex_topup_packages')->onDelete('set null');
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aurex_topup_requests');
        Schema::dropIfExists('aurex_topup_packages');
    }
};
