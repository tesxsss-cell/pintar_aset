<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan usulan perubahan untuk field/informasi tambahan (custom fields)
 * dalam bentuk JSON, mis. { "fields": [ { "label": "No. Mesin", "value": "..." } ] }.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_update_reports', function (Blueprint $table): void {
            $table->json('proposed_metadata')->nullable()->after('proposed_description');
        });
    }

    public function down(): void
    {
        Schema::table('asset_update_reports', function (Blueprint $table): void {
            $table->dropColumn('proposed_metadata');
        });
    }
};
