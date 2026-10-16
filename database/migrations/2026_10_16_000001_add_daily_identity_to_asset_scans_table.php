<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_scans', function (Blueprint $table): void {
            // Nullable agar log lama tetap tersimpan tanpa mengarang identitasnya.
            $table->string('visitor_hash', 64)->nullable();
            $table->date('scan_date')->nullable();
            $table->unique(['asset_id', 'visitor_hash', 'scan_date'], 'asset_scans_daily_unique');
            $table->index(['scan_date', 'asset_id'], 'asset_scans_date_asset_index');
        });
    }

    public function down(): void
    {
        Schema::table('asset_scans', function (Blueprint $table): void {
            $table->dropUnique('asset_scans_daily_unique');
            $table->dropIndex('asset_scans_date_asset_index');
            $table->dropColumn(['visitor_hash', 'scan_date']);
        });
    }
};
