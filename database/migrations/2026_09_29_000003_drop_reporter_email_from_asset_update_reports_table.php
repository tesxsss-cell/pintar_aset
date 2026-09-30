<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email pelapor tidak diperlukan, sehingga kolomnya dihapus sepenuhnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('asset_update_reports', 'reporter_email')) {
            Schema::table('asset_update_reports', function (Blueprint $table): void {
                $table->dropColumn('reporter_email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('asset_update_reports', function (Blueprint $table): void {
            $table->string('reporter_email')->nullable()->after('reporter_name');
        });
    }
};
