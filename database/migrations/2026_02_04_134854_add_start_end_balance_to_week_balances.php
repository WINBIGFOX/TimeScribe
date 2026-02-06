<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('week_balances', function (Blueprint $table): void {
            $table->integer('start_balance')->default(0)->after('balance');
            $table->integer('end_balance')->default(0)->after('start_balance');
        });
    }

    public function down(): void
    {
        Schema::table('week_balances', function (Blueprint $table): void {
            $table->dropColumn('start_balance');
            $table->dropColumn('end_balance');
        });
    }
};
