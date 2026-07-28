<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminExcelExporter
{
    /**
     * @param array<int, array{name: string, headers: array<int, mixed>, rows: array<int, array<int, mixed>>, meta?: array<int, array<int, mixed>>}> $sheets
     */
    public function build(array $sheets): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getProperties()
            ->setCreator('MyFC')
            ->setLastModifiedBy('MyFC')
            ->setTitle('MyFC 관리자 엑셀 다운로드');

        foreach ($sheets as $sheetIndex => $sheetData) {
            $sheet = $spreadsheet->createSheet($sheetIndex);
            $sheet->setTitle($this->sheetTitle((string) ($sheetData['name'] ?? 'Sheet' . ($sheetIndex + 1))));
            $rowNumber = 1;

            foreach ($sheetData['meta'] ?? [] as $metaRow) {
                $this->writeRow($sheet, $rowNumber++, $metaRow);
            }

            $headerRow = $rowNumber++;
            $headers = $sheetData['headers'] ?? [];
            $this->writeRow($sheet, $headerRow, $headers);

            foreach ($sheetData['rows'] ?? [] as $row) {
                $this->writeRow($sheet, $rowNumber++, $row);
            }

            $lastColumn = max(1, count($headers));
            $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumn);
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumnLetter . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->freezePane('A' . ($headerRow + 1));
            $sheet->setAutoFilter('A' . $headerRow . ':' . $lastColumnLetter . $headerRow);

            for ($column = 1; $column <= $lastColumn; $column++) {
                $letter = Coordinate::stringFromColumnIndex($column);
                $sheet->getColumnDimension($letter)->setWidth(20);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        if ($content === false) {
            throw new \RuntimeException('엑셀 파일을 생성할 수 없습니다.');
        }

        return $content;
    }

    private function writeRow($sheet, int $rowNumber, array $values): void
    {
        foreach (array_values($values) as $columnIndex => $value) {
            $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . $rowNumber;
            $sheet->setCellValueExplicit($coordinate, $this->normalizeValue($value), DataType::TYPE_STRING);
            $sheet->getStyle($coordinate)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        }
    }

    private function normalizeValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function sheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\/:*?\[\]]/', '_', $title) ?? 'Sheet';
        $title = trim($title);

        return mb_substr($title !== '' ? $title : 'Sheet', 0, 31);
    }
}
