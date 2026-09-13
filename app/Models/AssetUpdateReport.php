<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetUpdateReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'reporter_name', 'reporter_email', 'reporter_phone', 'reason',
        'evidence_image_path', 'proposed_name', 'proposed_owner', 'proposed_location',
        'proposed_condition', 'proposed_category', 'proposed_description', 'status',
        'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
