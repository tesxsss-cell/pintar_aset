<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetScan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['asset_id', 'ip_hash', 'user_agent', 'scanned_at'];

    protected function casts(): array
    {
        return ['scanned_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
