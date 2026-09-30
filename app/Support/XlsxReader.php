<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Pembaca file .xlsx tanpa dependensi eksternal (memakai ZipArchive + SimpleXML
 * bawaan PHP). Membaca nilai sel sekaligus mengekstrak gambar yang tertanam di
 * dalam sheet beserta baris tempat gambar tersebut ditempelkan.
 *
 * File .xlsx pada dasarnya adalah arsip ZIP berisi XML, sehingga tidak perlu
 * pustaka PhpSpreadsheet untuk kebutuhan impor sederhana ini.
 */
class XlsxReader
{
    /** @var array<int, string> */
    private array $sharedStrings = [];

    /** @var array<int, array<int, string>> baris => (kolom0 => teks) */
    private array $rows = [];

    /**
     * Gambar per baris (0-based Excel row) => daftar
     * [{col: int, tmp: string, ext: string}].
     *
     * @var array<int, array<int, array{col: int, tmp: string, ext: string}>>
     */
    private array $imagesByRow = [];

    private int $maxRow = 0;

    private int $maxCol = 0;

    public function __construct(private string $path)
    {
        $this->parse();
    }

    public static function open(string $path): self
    {
        return new self($path);
    }

    private function parse(): void
    {
        $zip = new ZipArchive();

        if ($zip->open($this->path) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        $this->loadSharedStrings($zip);
        $sheetPath = $this->firstSheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException('Lembar kerja pertama tidak ditemukan.');
        }

        $this->loadCells($sheetXml);
        $this->loadImages($zip, $sheetPath, $sheetXml);

        $zip->close();
    }

    private function loadSharedStrings(ZipArchive $zip): void
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return;
        }

        $doc = @simplexml_load_string($xml);

        if ($doc === false) {
            return;
        }

        foreach ($doc->si as $si) {
            $this->sharedStrings[] = $this->extractText($si);
        }
    }

    private function extractText(\SimpleXMLElement $node): string
    {
        // Kasus sederhana: <si><t>teks</t></si>
        if (isset($node->t)) {
            return (string) $node->t;
        }

        // Kasus rich text: gabungan beberapa <r><t>...</t></r>
        $text = '';
        foreach ($node->r as $run) {
            $text .= (string) $run->t;
        }

        return $text;
    }

    private function firstSheetPath(ZipArchive $zip): string
    {
        $wbRels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $wb = $zip->getFromName('xl/workbook.xml');

        if ($wb !== false && $wbRels !== false) {
            $wbDoc = @simplexml_load_string($wb);
            $relsDoc = @simplexml_load_string($wbRels);

            if ($wbDoc !== false && $relsDoc !== false) {
                $wbDoc->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $sheets = $wbDoc->xpath('//*[local-name()="sheet"]');
                $first = $sheets[0] ?? null;

                if ($first !== null) {
                    $rid = (string) $first->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;

                    foreach ($relsDoc->Relationship as $rel) {
                        if ((string) $rel['Id'] === $rid) {
                            return $this->normalizePath((string) $rel['Target'], 'xl/');
                        }
                    }
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function loadCells(string $sheetXml): void
    {
        $doc = @simplexml_load_string($sheetXml);

        if ($doc === false || ! isset($doc->sheetData)) {
            return;
        }

        foreach ($doc->sheetData->row as $row) {
            $rowIndex = (int) $row['r']; // 1-based
            $this->maxRow = max($this->maxRow, $rowIndex);

            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $col = $this->columnIndex($ref); // 0-based
                $this->maxCol = max($this->maxCol, $col);
                $type = (string) $c['t'];
                $value = '';

                if ($type === 'inlineStr') {
                    $value = isset($c->is) ? $this->extractText($c->is) : '';
                } elseif ($type === 's') {
                    $idx = (int) $c->v;
                    $value = $this->sharedStrings[$idx] ?? '';
                } elseif ($type === 'str') {
                    $value = (string) $c->v;
                } else {
                    $value = isset($c->v) ? (string) $c->v : '';
                }

                $this->rows[$rowIndex - 1][$col] = trim($value);
            }
        }
    }

    private function loadImages(ZipArchive $zip, string $sheetPath, string $sheetXml): void
    {
        // 1) Cari drawing yang terkait dengan sheet.
        $sheetDir = dirname($sheetPath);
        $sheetRels = $zip->getFromName($sheetDir.'/_rels/'.basename($sheetPath).'.rels');

        if ($sheetRels === false) {
            return;
        }

        $sheetDoc = @simplexml_load_string($sheetXml);
        $relsDoc = @simplexml_load_string($sheetRels);

        if ($sheetDoc === false || $relsDoc === false || ! isset($sheetDoc->drawing)) {
            return;
        }

        $drawingRid = (string) $sheetDoc->drawing->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
        $drawingPath = null;

        foreach ($relsDoc->Relationship as $rel) {
            if ((string) $rel['Id'] === $drawingRid) {
                $drawingPath = $this->normalizePath((string) $rel['Target'], $sheetDir.'/');
                break;
            }
        }

        if ($drawingPath === null) {
            return;
        }

        $drawingXml = $zip->getFromName($drawingPath);

        if ($drawingXml === false) {
            return;
        }

        // 2) Peta rId => file media dari rels drawing.
        $drawingDir = dirname($drawingPath);
        $drawingRels = $zip->getFromName($drawingDir.'/_rels/'.basename($drawingPath).'.rels');
        $ridToMedia = [];

        if ($drawingRels !== false) {
            $drDoc = @simplexml_load_string($drawingRels);

            if ($drDoc !== false) {
                foreach ($drDoc->Relationship as $rel) {
                    $ridToMedia[(string) $rel['Id']] = $this->normalizePath((string) $rel['Target'], $drawingDir.'/');
                }
            }
        }

        // 3) Telusuri tiap anchor (oneCellAnchor / twoCellAnchor).
        $drDoc = @simplexml_load_string($drawingXml);

        if ($drDoc === false) {
            return;
        }

        $anchors = $drDoc->xpath('//*[local-name()="oneCellAnchor" or local-name()="twoCellAnchor"]');

        foreach ($anchors as $anchor) {
            $from = $anchor->xpath('.//*[local-name()="from"]')[0] ?? null;

            if ($from === null) {
                continue;
            }

            $rowNode = $from->xpath('.//*[local-name()="row"]')[0] ?? null;
            $colNode = $from->xpath('.//*[local-name()="col"]')[0] ?? null;
            $blip = $anchor->xpath('.//*[local-name()="blip"]')[0] ?? null;

            if ($rowNode === null || $blip === null) {
                continue;
            }

            $embed = (string) $blip->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->embed;
            $mediaPath = $ridToMedia[$embed] ?? null;

            if ($mediaPath === null) {
                continue;
            }

            $bytes = $zip->getFromName($mediaPath);

            if ($bytes === false) {
                continue;
            }

            $tmp = tempnam(sys_get_temp_dir(), 'xlsximg_');
            file_put_contents($tmp, $bytes);

            $this->imagesByRow[(int) $rowNode][] = [
                'col' => $colNode !== null ? (int) $colNode : 0,
                'tmp' => $tmp,
                'ext' => strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION) ?: 'png'),
            ];
        }
    }

    private function normalizePath(string $target, string $base): string
    {
        if (str_starts_with($target, '/')) {
            return ltrim($target, '/');
        }

        $path = $base.$target;
        $parts = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($parts);

                continue;
            }

            $parts[] = $segment;
        }

        return implode('/', $parts);
    }

    private function columnIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $letters = $m[1] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $ch) {
            $index = $index * 26 + (ord($ch) - 64);
        }

        return $index - 1;
    }

    /** @return array<int, array<int, string>> */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * Nilai satu baris (0-based) sebagai array padat 0..maxCol.
     *
     * @return array<int, string>
     */
    public function row(int $index): array
    {
        $out = [];

        for ($c = 0; $c <= $this->maxCol; $c++) {
            $out[$c] = $this->rows[$index][$c] ?? '';
        }

        return $out;
    }

    /** @return array<int, array{col: int, tmp: string, ext: string}> */
    public function imagesForRow(int $rowIndex): array
    {
        return $this->imagesByRow[$rowIndex] ?? [];
    }

    public function maxRow(): int
    {
        return $this->maxRow;
    }

    public function maxCol(): int
    {
        return $this->maxCol;
    }
}


