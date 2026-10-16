<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AssetScan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['asset_id', 'visitor_hash', 'scan_date', 'ip_hash', 'user_agent', 'scanned_at'];

    protected function casts(): array
    {
        return ['scanned_at' => 'datetime', 'scan_date' => 'date'];
    }

    // Simpan tanggal kalender murni, termasuk pada SQLite yang tidak memaksa tipe DATE.
    // Tanpa mutator ini, cast date menambahkan 00:00:00 dan merusak pencarian unique.
    protected function scanDate(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value === null ? null : Carbon::parse($value)->toDateString(),
        );
    }

    /**
     * Rentang kalender harian yang dipakai bersama oleh kartu dan tabel.
     * Batas akhir eksklusif juga mencakup tanggal yang tersimpan sebagai midnight.
     */
    public function scopeDailyBetween(Builder $query, string $firstDate, string $lastDate): Builder
    {
        return $query->whereNotNull('visitor_hash')->whereNotNull('scan_date')
            ->where('scan_date', '>=', $firstDate)
            ->where('scan_date', '<', Carbon::parse($lastDate)->addDay()->toDateString());
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
