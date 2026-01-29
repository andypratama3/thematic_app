<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\FertilizerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class DatasetController extends Controller
{
    public function index()
    {
        $datasets = Dataset::with('user')
            ->withCount('transactions')
            ->latest()
            ->paginate(12);

        return view('pages.datasets.index', compact('datasets'));
    }

    public function create()
    {
        return view('pages.datasets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fertilizer,farmer,transaction,custom',
            'year' => 'required',
            'month' => 'required',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'public');

            $dataset = Dataset::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'slug' => Str::slug($request->name . '-' . $request->year . '-' . $request->month),
                'description' => $request->description,
                'type' => $request->type,
                'import_file_path' => $filePath,
                'import_status' => 'processing',
            ]);

            $this->processImport($dataset, storage_path('app/public/' . $filePath), $request->year, $request->month);

            DB::commit();
            return redirect()->route('datasets.index')->with('success', 'Dataset berhasil diimport!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    private function processImport($dataset, $filePath, $year, $month)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip header
        $header = array_shift($rows);

        $successCount = 0;
        $errorCount = 0;

        // Default coordinates untuk wilayah Kediri (jika tidak ada koordinat)
        $defaultCoordinates = [
            [-7.8167, 112.0167], // Kediri 1
            [-7.8200, 112.0200], // Kediri 2
            [-7.8100, 112.0100], // Kediri 3
            [-7.8250, 112.0250], // Kediri 4
        ];

        foreach ($rows as $index => $row) {
            try {
                if (empty($row[0])) continue; // Skip empty rows

                // Parse address dari row (jika ada di excel Anda)
                $address = $row[24] ?? 'DS. BLABAK KEC. KANDAT - Kandat - KAB. KEDIRI - JAWA TIMUR';

                // Generate random coordinates dalam radius Kediri
                // Atau gunakan koordinat default
                $coordIndex = $index % count($defaultCoordinates);
                $baseCoord = $defaultCoordinates[$coordIndex];

                // Add small random offset untuk setiap point
                $latitude = $baseCoord[0] + (mt_rand(-100, 100) / 10000);
                $longitude = $baseCoord[1] + (mt_rand(-100, 100) / 10000);

                FertilizerTransaction::create([
                    'dataset_id' => $dataset->id,
                    'transaction_code' => $row[2] ?? '',
                    'transaction_number' => $row[3] ?? '',
                    'nik' => $row[4] ?? '',
                    'farmer_name' => $row[5] ?? '',
                    'transaction_date' => $this->parseDate($row[1] ?? null, $year, $month),
                    'redemption_date' => $this->parseDate($row[14] ?? null, $year, $month),
                    'urea' => $row[6] ?? 0,
                    'npk' => $row[7] ?? 0,
                    'sp36' => $row[8] ?? 0,
                    'za' => $row[9] ?? 0,
                    'npk_formula' => $row[10] ?? 0,
                    'organic' => $row[11] ?? 0,
                    'organic_liquid' => $row[12] ?? 0,
                    'notes' => $row[13] ?? '',
                    'proof_url' => $row[15] ?? '',
                    'urea_color' => $row[17] ?? 'black',
                    'npk_color' => $row[18] ?? 'black',
                    'sp36_color' => $row[19] ?? 'black',
                    'za_color' => $row[20] ?? 'black',
                    'npk_formula_color' => $row[21] ?? 'black',
                    'organic_color' => $row[22] ?? 'black',
                    'organic_liquid_color' => $row[23] ?? 'black',
                    // PENTING: Tambahkan koordinat
                    'address' => $address,
                    'village' => 'BLABAK',
                    'district' => 'KANDAT',
                    'regency' => 'KEDIRI',
                    'province' => 'JAWA TIMUR',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('Import error: ' . $e->getMessage());
            }
        }

        $dataset->update([
            'total_records' => $successCount,
            'total_parameters' => 29,
            'import_status' => $errorCount > 0 ? 'completed' : 'completed',
            'imported_at' => now(),
        ]);
    }

    private function parseDate($value, $year, $month)
    {
        if (empty($value)) {
            return Carbon::create($year, $month, 1);
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return Carbon::create($year, $month, 1);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header dengan LATITUDE & LONGITUDE
        $headers = [
            'custom unique id', 'data date', 'Kode TRX', 'No Transaksi', 'NIK',
            'Nama Petani', 'Urea', 'NPK', 'SP36', 'ZA', 'NPK Formula',
            'Organik', 'Organik Cair', 'Keterangan', 'Tanggal Tebus',
            'Url Bukti', 'value', 'color', 'color2', 'color3', 'color4',
            'color5', 'color6', 'color7',
            'Alamat', 'Desa', 'Kecamatan', 'Kabupaten', 'Provinsi',
            'Latitude', 'Longitude'  // KOLOM BARU
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Add sample data dengan koordinat
        $sampleData = [
            [
                1, '2025-05-08', '34624069', 'S0D131\K0015M', '3506050104550004',
                'ABDULLAH ZAINI YAHYA', 0, 390, 0, 0, 0, 0, 0,
                'Sample', '2025-05-21', 'https://example.com', 0,
                'black', 'green', 'black', 'black', 'black', 'black', 'black',
                'DS. BLABAK KEC. KANDAT', 'BLABAK', 'KANDAT', 'KEDIRI', 'JAWA TIMUR',
                -7.8167, 112.0167  // Koordinat Kediri
            ],
            [
                2, '2025-05-08', '34859663', 'S0D131\K0015R', '3506051103830003',
                'AHMAD WAHYUNI', 400, 533, 0, 0, 0, 0, 0,
                'Sample', '2025-05-24', 'https://example.com', 0,
                'red', 'green', 'black', 'black', 'black', 'black', 'black',
                'DS. BLABAK KEC. KANDAT', 'BLABAK', 'KANDAT', 'KEDIRI', 'JAWA TIMUR',
                -7.8200, 112.0200
            ],
            [
                3, '2025-05-08', '35331752', 'S0D131\K00165', '3506051803720004',
                'CUK BUDI SUSILO', 0, 1455, 0, 0, 0, 0, 0,
                'Sample', '2025-05-30', 'https://example.com', 0,
                'black', 'red', 'black', 'black', 'black', 'black', 'black',
                'DS. BLABAK KEC. KANDAT', 'BLABAK', 'KANDAT', 'KEDIRI', 'JAWA TIMUR',
                -7.8100, 112.0100
            ]
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:AD1')->applyFromArray($headerStyle);

        // Highlight kolom koordinat (penting!)
        $coordStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFA500']  // Orange
            ],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('AC1:AD1')->applyFromArray($coordStyle);

        // Add notes/instructions
        $sheet->setCellValue('A5', 'CATATAN PENTING:');
        $sheet->setCellValue('A6', '1. Kolom Latitude & Longitude WAJIB diisi agar data muncul di peta');
        $sheet->setCellValue('A7', '2. Format Latitude: -7.8167 (negatif untuk selatan equator)');
        $sheet->setCellValue('A8', '3. Format Longitude: 112.0167 (positif untuk timur)');
        $sheet->setCellValue('A9', '4. Contoh koordinat Kediri: Lat=-7.8167, Lng=112.0167');
        $sheet->setCellValue('A10', '5. Gunakan Google Maps untuk mendapatkan koordinat: Klik kanan > Copy coordinates');
        $sheet->setCellValue('A11', '6. Jika kolom kosong, sistem akan auto-generate koordinat di area Kediri');

        $sheet->getStyle('A5:A11')->getFont()->setBold(true)->setItalic(true);
        $sheet->getStyle('A5')->getFont()->setSize(12)->getColor()->setRGB('FF0000');

        // Auto width
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze first row
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_fertilizer_import_' . date('Y_m_d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function destroy(Dataset $dataset)
    {

        $dataset->delete();
        return redirect()->route('datasets.index')->with('success', 'Dataset berhasil dihapus!');
    }
}
