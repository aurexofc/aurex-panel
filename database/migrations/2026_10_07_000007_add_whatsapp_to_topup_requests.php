<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add WhatsApp number to top-up requests so the admin can notify
     * users about approval/rejection via WhatsApp (CallMeBot).
     */
    public function up(): void
    {
        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable()->after('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
        });
    }
};
