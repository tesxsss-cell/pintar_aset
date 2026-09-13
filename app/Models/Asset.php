<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = ['code','slug','name','owner','location','category','condition','description','notes','acquired_at','image_path','active'];
    protected function casts(): array { return ['acquired_at' => 'date', 'active' => 'boolean']; }
    public function reports(): HasMany { return $this->hasMany(AssetUpdateReport::class); }
    public function getRouteKeyName(): string { return 'slug'; }
}
