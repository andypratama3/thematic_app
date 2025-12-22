<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExportService
{
    /**
     * Export transactions to Excel
     */
    public function exportTransactions($transactions, $fileName = 'transactions_export.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'ID', 'Transaction Code', 'Transaction Number', 'NIK', 'Farmer Name',
            'Transaction Date', 'Urea (kg)', 'NPK (kg)', 'SP36 (kg)', 'ZA (kg)',
            'NPK Formula (kg)', 'Organic (kg)', 'Organic Liquid (kg)', 'Total (kg)',
            'Address', 'Village', 'District', 'Regency', 'Province',
            'Latitude', 'Longitude', 'Notes'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $sheet->getStyle('A1:V1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction->id);
            $sheet->setCellValue('B' . $row, $transaction->transaction_code);
            $sheet->setCellValue('C' . $row, $transaction->transaction_number);
            $sheet->setCellValue('D' . $row, $transaction->nik);
            $sheet->setCellValue('E' . $row, $transaction->farmer_name);
            $sheet->setCellValue('F' . $row, $transaction->transaction_date->format('Y-m-d'));
            $sheet->setCellValue('G' . $row, $transaction->urea);
            $sheet->setCellValue('H' . $row, $transaction->npk);
            $sheet->setCellValue('I' . $row, $transaction->sp36);
            $sheet->setCellValue('J' . $row, $transaction->za);
            $sheet->setCellValue('K' . $row, $transaction->npk_formula);
            $sheet->setCellValue('L' . $row, $transaction->organic);
            $sheet->setCellValue('M' . $row, $transaction->organic_liquid);
            $sheet->setCellValue('N' . $row, $transaction->total_fertilizer);
            $sheet->setCellValue('O' . $row, $transaction->address);
            $sheet->setCellValue('P' . $row, $transaction->village);
            $sheet->setCellValue('Q' . $row, $transaction->district);
            $sheet->setCellValue('R' . $row, $transaction->regency);
            $sheet->setCellValue('S' . $row, $transaction->province);
            $sheet->setCellValue('T' . $row, $transaction->latitude);
            $sheet->setCellValue('U' . $row, $transaction->longitude);
            $sheet->setCellValue('V' . $row, $transaction->notes);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply borders to all data
        $sheet->getStyle('A1:V' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Create writer and save
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export statistics to Excel
     */
    public function exportStatistics($stats, $fileName = 'statistics_export.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Statistics Report');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $row = 3;
        foreach ($stats as $key => $value) {
            $sheet->setCellValue('A' . $row, ucwords(str_replace('_', ' ', $key)));
            $sheet->setCellValue('B' . $row, $value);
            $row++;
        }

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
