<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the user's real email to top-up requests so approval/rejection
     * notifications go to an email the user actually checks (account
     * emails may be fake or unchecked).
     */
    public function up(): void
    {
        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
