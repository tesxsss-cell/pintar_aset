<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetUpdateReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'reporter_name', 'reporter_phone', 'reason',
        'evidence_image_path', 'proposed_name', 'proposed_owner', 'proposed_location',
        'proposed_condition', 'proposed_category', 'proposed_description', 'proposed_metadata',
        'status', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'proposed_metadata' => 'array'];
    }

    /**
     * Usulan perubahan field tambahan (custom fields) dari pelapor.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function proposedFields(): array
    {
        $fields = data_get($this->proposed_metadata, 'fields', []);

        return is_array($fields) ? array_values(array_filter($fields, static fn ($f) => is_array($f) && ($f['label'] ?? '') !== '')) : [];
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
