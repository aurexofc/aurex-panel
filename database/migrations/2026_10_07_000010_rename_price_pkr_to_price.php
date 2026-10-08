<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aurex_topup_packages', function (Blueprint $table) {
            $table->renameColumn('price_pkr', 'price');
        });

        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->renameColumn('price_pkr', 'price');
        });
    }

    public function down(): void
    {
        Schema::table('aurex_topup_packages', function (Blueprint $table) {
            $table->renameColumn('price', 'price_pkr');
        });

        Schema::table('aurex_topup_requests', function (Blueprint $table) {
            $table->renameColumn('price', 'price_pkr');
        });
    }
};
