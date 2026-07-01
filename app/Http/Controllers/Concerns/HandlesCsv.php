<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HandlesCsv
{
    /**
     * Stream sebuah file Excel (.xlsx).
     *
     * @param  array<int,string>  $headers
     * @param  iterable<int,array<int,mixed>>  $rows
     */
    protected function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tulis header (baris 1) + styling tebal & latar abu
        $sheet->fromArray($headers, null, 'A1');
        $lastCol = $sheet->getHighestColumn();
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');

        // Tulis data mulai baris 2
        $r = 2;
        foreach ($rows as $row) {
            $sheet->fromArray((array) $row, null, 'A'.$r);
            $r++;
        }

        // Auto-size kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Baca file Excel/CSV yang di-upload menjadi array baris (header dilewati).
     *
     * @return array<int,array<int,string>>
     */
    protected function readCsv(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($file->getRealPath())->getActiveSheet();

        $rows = [];
        $isFirst = true;
        foreach ($sheet->toArray(null, true, false, false) as $data) {
            if ($isFirst) {
                $isFirst = false; // lewati header
                continue;
            }
            // lewati baris kosong
            if (count(array_filter($data, fn ($v) => $v !== null && trim((string) $v) !== '')) === 0) {
                continue;
            }
            $rows[] = array_map(fn ($v) => trim((string) $v), $data);
        }

        return $rows;
    }

    /**
     * Konversi teks angka ke float, tahan format Indonesia.
     * Titik dianggap pemisah ribuan, koma sebagai desimal.
     * Contoh: "10.000" => 10000, "1.500,50" => 1500.5, "10000" => 10000.
     */
    protected function csvNumber(?string $value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }
        $value = str_replace([' ', '.'], '', $value); // buang spasi & titik ribuan
        $value = str_replace(',', '.', $value);        // koma -> titik desimal

        return (float) $value;
    }

    /**
     * Konversi nilai teks boolean (1/0/ya/tidak/true/false) ke bool.
     */
    protected function csvBool(?string $value, bool $default = true): bool
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return $default;
        }

        return in_array($value, ['1', 'ya', 'yes', 'true', 'aktif', 'y'], true);
    }
}
