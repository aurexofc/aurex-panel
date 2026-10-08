<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aurex: remove the support ticket feature entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('aurex_ticket_messages');
        Schema::dropIfExists('aurex_tickets');
    }

    public function down(): void
    {
        // Feature removed intentionally; nothing to restore.
    }
};
