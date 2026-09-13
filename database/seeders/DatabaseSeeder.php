<?php
namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetUpdateReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{public function run(): void
    {$admin = User::updateOrCreate(['email' => 'admin@pintaraset.test'], ['name' => 'Admin Aset', 'password' => Hash::make('password')]);
    $items                            = [['AST-001', 'MacBook Pro 14', 'Tim Produk', 'Ruang Kerja A · Meja 07', 'Laptop', 'Baik'], ['AST-002', 'Proyektor Epson EB-X06', 'Kantor', 'Ruang Rapat Garuda', 'Elektronik', 'Perlu Perbaikan'], ['AST-003', 'Kursi Ergonomis', 'Dina Putri', 'Ruang Kerja B · Meja 12', 'Furnitur', 'Baik'], ['AST-004', 'Kamera Sony A6400', 'Tim Kreatif', 'Lemari Peralatan 2', 'Kamera', 'Baik']];foreach ($items as $i => $v) {$a = Asset::updateOrCreate(['code' => $v[0]], ['slug' => strtolower($v[0]) . '-' . ($i + 101), 'name' => $v[1], 'owner' => $v[2], 'location' => $v[3], 'category' => $v[4], 'condition' => $v[5], 'description' => 'Aset operasional kantor yang tercatat dalam sistem inventaris.', 'acquired_at' => now()->subMonths(4 + $i), 'active' => true]);if ($i === 1) {
        AssetUpdateReport::firstOrCreate(['asset_id' => $a->id, 'reporter_name' => 'Raka'], ['reason' => 'Gambar mulai redup dan lokasi alat sudah dipindah.', 'proposed_location' => 'Ruang Rapat Merpati', 'proposed_condition' => 'Perlu Perbaikan', 'status' => 'pending']);
    }}}}
