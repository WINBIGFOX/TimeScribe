<?php

declare(strict_types=1);

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
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->default('#000000');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::table('timestamps', function (Blueprint $table): void {
            $table->foreignId('tag_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timestamps', function (Blueprint $table): void {
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
        });
        Schema::dropIfExists('tag');
    }
};
