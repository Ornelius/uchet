<?php

namespace App\Services;

use App\Models\Equipment;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generate(Equipment $equipment): string
    {
        $matrix = $this->getMatrix($this->getUrl($equipment));

        $path = 'qr/equipment-'.$equipment->id.'.svg';

        Storage::disk('public')->put($path, $this->toSvg($matrix));
        Storage::disk('public')->put(str_replace('.svg', '.png', $path), $this->toPng($matrix, 8));

        return $path;
    }

    public function getUrl(Equipment $equipment): string
    {
        return rtrim(config('app.url'), '/').'/admin/equipment/'.$equipment->id;
    }

    protected function getMatrix(string $data): array
    {
        $options = new QROptions([
            'eccLevel' => EccLevel::M,
        ]);

        $qrCode = new QRCode($options);
        $qrCode->addByteSegment($data);

        return $qrCode->getMatrix()->getMatrix(true);
    }

    protected function toSvg(array $matrix, int $module = 10): string
    {
        $quiet = 4;
        $size = (count($matrix) + 2 * $quiet) * $module;
        $rects = [];

        foreach ($matrix as $y => $row) {
            $x = 0;

            while ($x < count($row)) {
                if (! $row[$x]) {
                    $x++;

                    continue;
                }

                $start = $x;

                while ($x < count($row) && $row[$x]) {
                    $x++;
                }

                $width = ($x - $start) * $module;
                $px = ($start + $quiet) * $module;
                $py = ($y + $quiet) * $module;

                $rects[] = sprintf('<rect x="%d" y="%d" width="%d" height="%d"/>', $px, $py, $width, $module);
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$size.' '.$size
            .' shape-rendering="crispEdges" class="qr-svg">'
            .'<rect width="100%" height="100%" fill="#ffffff"/>'
            .'<g fill="#000000">'.implode('', $rects).'</g>'
            .'</svg>';
    }

    protected function toPng(array $matrix, int $scale = 8): string
    {
        $size = count($matrix) * $scale;

        $raw = '';

        foreach ($matrix as $row) {
            $raw .= "\x00";

            foreach ($row as $cell) {
                $raw .= str_repeat($cell ? "\x00" : "\xff", $scale);
            }
        }

        $signature = "\x89PNG\r\n\x1a\n";
        $ihdr = $this->pngChunk('IHDR', pack('NNCC', $size, $size, 8, 0));
        $idat = $this->pngChunk('IDAT', gzcompress($raw, 9));
        $iend = $this->pngChunk('IEND', '');

        return $signature.$ihdr.$idat.$iend;
    }

    protected function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }
}
