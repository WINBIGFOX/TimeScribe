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
        Schema::create('work_schedules', function (Blueprint $table): void {
            $table->id();
            $table->decimal('sunday')->default(0);
            $table->decimal('monday')->default(0);
            $table->decimal('tuesday')->default(0);
            $table->decimal('wednesday')->default(0);
            $table->decimal('thursday')->default(0);
            $table->decimal('friday')->default(0);
            $table->decimal('saturday')->default(0);
            $table->date('valid_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
