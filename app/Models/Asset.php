<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = ['code','slug','name','owner','location','category','condition','description','notes','acquired_at','image_path','metadata','active'];

    protected function casts(): array
    {
        return ['acquired_at' => 'date', 'active' => 'boolean', 'metadata' => 'array'];
    }

    /**
     * Field kustom dinamis (key-value) yang tersimpan di kolom JSON "metadata".
     * Nilai bertipe array daftar {label, value} agar mudah ditampilkan berurutan.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function customFields(): array
    {
        $fields = data_get($this->metadata, 'fields', []);

        return is_array($fields) ? array_values(array_filter($fields, static fn ($f) => is_array($f) && ($f['label'] ?? '') !== '')) : [];
    }

    /**
     * Foto tambahan hasil impor Excel (mis. foto STNK) selain foto utama.
     *
     * @return array<int, array{label: string, path: string}>
     */
    public function extraPhotos(): array
    {
        $photos = data_get($this->metadata, 'photos', []);

        return is_array($photos) ? array_values(array_filter($photos, static fn ($p) => is_array($p) && ($p['path'] ?? '') !== '')) : [];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AssetUpdateReport::class);
    }

    public function scanEvents(): HasMany
    {
        return $this->hasMany(AssetScan::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
