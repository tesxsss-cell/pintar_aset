<?php

namespace App\Services;

use App\Models\Asset;
use App\Support\XlsxReader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengimpor aset dari file Excel (.xlsx).
 *
 * - Kolom yang dikenal dipetakan ke field baku tabel "assets".
 * - Kolom lain (mis. No. Mesin, No. Rangka, No. Pol) otomatis masuk ke
 *   kolom JSON "metadata" sebagai field dinamis (key-value) tanpa perlu
 *   mengubah skema database.
 * - Foto yang tertanam di dalam Excel diekstrak otomatis; foto pada kolom
 *   "Foto" menjadi foto utama, foto lain (mis. STNK) disimpan sebagai foto
 *   tambahan di metadata.
 */
class AssetImportService
{
    /**
     * Alias header (dinormalisasi) => field baku.
     *
     * @var array<string, string>
     */
    private array $aliases = [
        'kode' => 'code', 'kode aset' => 'code', 'kode barang' => 'code', 'code' => 'code',
        'nama' => 'name', 'nama barang' => 'name', 'nama aset' => 'name', 'name' => 'name',
        'pengguna' => 'owner', 'pemakai' => 'owner', 'pemilik' => 'owner', 'penanggung jawab' => 'owner',
        'pemilik/penanggung jawab' => 'owner', 'pemilik / penanggung jawab' => 'owner', 'owner' => 'owner',
        'lokasi' => 'location', 'lokasi saat ini' => 'location', 'location' => 'location',
        'kategori' => 'category', 'category' => 'category', 'jenis' => 'category',
        'kondisi' => 'condition', 'condition' => 'condition', 'keadaan' => 'condition',
        'deskripsi' => 'description', 'description' => 'description', 'keterangan' => 'description',
        'catatan' => 'notes', 'catatan internal' => 'notes', 'notes' => 'notes',
        'tanggal perolehan' => 'acquired_at', 'tgl perolehan' => 'acquired_at', 'tanggal' => 'acquired_at', 'acquired' => 'acquired_at',
        'foto' => 'image', 'foto barang' => 'image', 'foto aset' => 'image', 'gambar' => 'image', 'image' => 'image', 'photo' => 'image',
    ];

    /** Header yang diabaikan (nomor urut). */
    private array $ignored = ['no', 'no.', 'nomor', 'no urut', 'nomor urut', 'nomer'];

    private array $conditions = ['Baik', 'Perlu Perbaikan', 'Rusak', 'Hilang'];

    /**
     * @return array{created:int, photos:int, custom_fields:array<int,string>, skipped:int}
     */
    public function import(string $path): array
    {
        $reader = XlsxReader::open($path);
        $headerRow = $this->detectHeaderRow($reader);
        $header = $reader->row($headerRow);

        // Petakan tiap indeks kolom => field baku atau label custom.
        $map = [];          // colIndex => ['type'=>'base','field'=>...] | ['type'=>'custom','label'=>...] | ['type'=>'image']
        $imageCol = null;
        foreach ($header as $col => $raw) {
            $label = $this->cleanLabel($raw);

            if ($label === '') {
                continue;
            }

            $norm = $this->normalize($label);

            if (in_array($norm, $this->ignored, true)) {
                continue;
            }

            if (isset($this->aliases[$norm])) {
                $field = $this->aliases[$norm];

                if ($field === 'image') {
                    $imageCol = $col;
                    $map[$col] = ['type' => 'image'];
                } else {
                    $map[$col] = ['type' => 'base', 'field' => $field];
                }

                continue;
            }

            $map[$col] = ['type' => 'custom', 'label' => $label];
        }

        $created = 0;
        $photosCount = 0;
        $skipped = 0;
        $customFieldsSeen = [];

        for ($r = $headerRow + 1; $r < $reader->maxRow(); $r++) {
            $row = $reader->row($r);
            $rowImages = $reader->imagesForRow($r);

            $base = [];
            $fields = [];

            foreach ($map as $col => $meta) {
                $value = trim((string) ($row[$col] ?? ''));

                if ($meta['type'] === 'base') {
                    if ($value !== '') {
                        $base[$meta['field']] = $value;
                    }
                } elseif ($meta['type'] === 'custom') {
                    if ($value !== '') {
                        $fields[] = ['label' => $meta['label'], 'value' => $this->cleanValue($value)];
                        $customFieldsSeen[$meta['label']] = true;
                    }
                }
            }

            $hasContent = ($base['name'] ?? '') !== ''
                || ! empty($fields)
                || ! empty($rowImages)
                || array_filter($base, static fn ($v) => $v !== '');

            if (! $hasContent) {
                $skipped++;

                continue;
            }

            // Foto: utama dari kolom "Foto", sisanya jadi foto tambahan.
            [$primaryPath, $extraPhotos] = $this->storeImages($rowImages, $imageCol, $header);

            if ($primaryPath !== null) {
                $photosCount++;
            }

            $metadata = [];
            if (! empty($fields)) {
                $metadata['fields'] = $fields;
            }
            if (! empty($extraPhotos)) {
                $metadata['photos'] = $extraPhotos;
            }

            $name = $base['name'] ?? ('Aset '.($r - $headerRow));
            $condition = $this->normalizeCondition($base['condition'] ?? null);

            $asset = new Asset();
            $asset->code = $this->uniqueCode($base['code'] ?? null);
            $asset->name = $name;
            $asset->owner = $base['owner'] ?? '-';
            $asset->location = $base['location'] ?? '-';
            $asset->category = $base['category'] ?? 'Umum';
            $asset->condition = $condition;
            $asset->description = $base['description'] ?? null;
            $asset->notes = $base['notes'] ?? null;
            $asset->acquired_at = $this->parseDate($base['acquired_at'] ?? null);
            $asset->image_path = $primaryPath;
            $asset->metadata = $metadata ?: null;
            $asset->active = true;
            $asset->slug = Str::slug($asset->code.'-'.$asset->name).'-'.Str::lower(Str::random(5));
            $asset->save();

            $created++;
        }

        return [
            'created' => $created,
            'photos' => $photosCount,
            'custom_fields' => array_keys($customFieldsSeen),
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<int, array{col:int,tmp:string,ext:string}>  $images
     * @param  array<int, string>  $header
     * @return array{0: ?string, 1: array<int, array{label:string,path:string}>}
     */
    private function storeImages(array $images, ?int $imageCol, array $header): array
    {
        if (empty($images)) {
            return [null, []];
        }

        $primary = null;
        $primaryIndex = null;

        // Pilih foto utama: yang tepat di kolom "Foto".
        if ($imageCol !== null) {
            foreach ($images as $i => $img) {
                if ($img['col'] === $imageCol) {
                    $primary = $img;
                    $primaryIndex = $i;
                    break;
                }
            }
        }

        // Bila tidak ada, ambil gambar pertama sebagai utama.
        if ($primary === null) {
            $primaryIndex = array_key_first($images);
            $primary = $images[$primaryIndex];
        }

        $primaryPath = $this->persist($primary);

        $extra = [];
        foreach ($images as $i => $img) {
            if ($i === $primaryIndex) {
                continue;
            }

            $path = $this->persist($img);

            if ($path === null) {
                continue;
            }

            $colLabel = $this->cleanLabel($header[$img['col']] ?? '');
            $extra[] = [
                'label' => $colLabel !== '' ? 'Foto '.$colLabel : 'Foto tambahan',
                'path' => $path,
            ];
        }

        return [$primaryPath, $extra];
    }

    /**
     * @param  array{col:int,tmp:string,ext:string}  $img
     */
    private function persist(array $img): ?string
    {
        if (! is_file($img['tmp'])) {
            return null;
        }

        $ext = in_array($img['ext'], ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $img['ext'] : 'png';
        $relative = 'assets/imported/'.Str::uuid()->toString().'.'.$ext;
        Storage::disk('public')->put($relative, file_get_contents($img['tmp']));
        @unlink($img['tmp']);

        return $relative;
    }

    private function detectHeaderRow(XlsxReader $reader): int
    {
        $best = 0;
        $bestScore = -1;
        $limit = min(15, $reader->maxRow());

        for ($r = 0; $r < $limit; $r++) {
            $row = $reader->row($r);
            $score = 0;
            $nonEmpty = 0;

            foreach ($row as $cell) {
                $label = $this->cleanLabel($cell);

                if ($label === '') {
                    continue;
                }

                $nonEmpty++;
                $norm = $this->normalize($label);

                if (isset($this->aliases[$norm]) || in_array($norm, $this->ignored, true)) {
                    $score += 2;
                }
            }

            // Utamakan baris dengan banyak header yang cocok; pertimbangkan
            // juga baris padat sebagai kandidat header.
            $total = $score + $nonEmpty;

            if ($score >= 2 && $total > $bestScore) {
                $bestScore = $total;
                $best = $r;
            }
        }

        return $best;
    }

    private function uniqueCode(?string $code): string
    {
        $code = $code !== null ? trim($code) : '';

        if ($code !== '' && ! Asset::where('code', $code)->exists()) {
            return $code;
        }

        do {
            $candidate = 'IMP-'.strtoupper(Str::random(6));
        } while (Asset::where('code', $candidate)->exists());

        return $candidate;
    }

    private function normalizeCondition(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'Baik';
        }

        $norm = $this->normalize($value);

        foreach ($this->conditions as $c) {
            if ($this->normalize($c) === $norm) {
                return $c;
            }
        }

        return match (true) {
            str_contains($norm, 'hilang') => 'Hilang',
            str_contains($norm, 'rusak') => 'Rusak',
            str_contains($norm, 'perbaikan') || str_contains($norm, 'servis') => 'Perlu Perbaikan',
            default => 'Baik',
        };
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        // Serial number Excel (hari sejak 1899-12-30).
        if (is_numeric($value) && (float) $value > 59 && (float) $value < 60000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value);
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function cleanLabel(string $raw): string
    {
        $raw = str_replace(["\r", "\n", "\t"], ' ', $raw);
        $raw = trim(preg_replace('/\s+/', ' ', $raw));

        return rtrim($raw, ' *:');
    }

    private function cleanValue(string $raw): string
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        return trim(preg_replace('/[ \t]+/', ' ', $raw));
    }

    private function normalize(string $value): string
    {
        return strtolower($this->cleanLabel($value));
    }
}

