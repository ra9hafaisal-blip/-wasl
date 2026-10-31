<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quote_likes', 'quote_reposts'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->primary(['quote_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_reposts');
        Schema::dropIfExists('quote_likes');
    }
};
