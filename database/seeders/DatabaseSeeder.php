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
   
    }}
