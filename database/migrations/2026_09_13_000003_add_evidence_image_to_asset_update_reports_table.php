<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_update_reports', function (Blueprint $table): void {
            $table->string('evidence_image_path')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('asset_update_reports', function (Blueprint $table): void {
            $table->dropColumn('evidence_image_path');
        });
    }
};
