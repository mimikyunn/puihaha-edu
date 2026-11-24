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
        Schema::table('membership_levels', function (Blueprint $table): void {
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedInteger('duration_months')->default(12);
            $table->text('benefits')->nullable();
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->foreignId('membership_level_id')->nullable()->constrained('membership_levels');
            $table->unsignedInteger('duration_months')->default(12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('membership_level_id');
            $table->dropColumn('duration_months');
        });

        Schema::table('membership_levels', function (Blueprint $table): void {
            $table->dropColumn(['fee', 'duration_months', 'benefits']);
        });
    }
};
