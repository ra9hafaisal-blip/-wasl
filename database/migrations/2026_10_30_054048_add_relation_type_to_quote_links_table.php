<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quote_links', function (Blueprint $table) {
            $table->string('relation_type', 20)->default('related');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_links', function (Blueprint $table) {
            $table->dropColumn('relation_type');
        });
    }
};
