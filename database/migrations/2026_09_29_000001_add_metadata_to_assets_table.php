<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom JSON "metadata" sebagai wadah dinamis untuk
     * atribut tambahan (custom fields) tiap aset, mis. No. Mesin,
     * No. Rangka, No. Pol, dsb. Struktur tabel utama tidak berubah,
     * sehingga fitur QR, laporan, dan log pemindaian tetap berjalan.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->json('metadata')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->dropColumn('metadata');
        });
    }
};
