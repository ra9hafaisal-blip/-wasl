<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->string('source')->nullable();
            $table->text('note')->nullable();
            $table->string('category', 50)->default('Mathematics');
            $table->timestamps();
        });

        Schema::create('quote_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('related_quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->unique(['quote_id', 'related_quote_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_links');
        Schema::dropIfExists('quotes');
    }
};
