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
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
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

        foreach ($rows as $row) {
            try {
                if (empty($row[0])) continue; // Skip empty rows

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
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('Import error: ' . $e->getMessage());
            }
        }

        $dataset->update([
            'total_records' => $successCount,
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

        // Set header
        $headers = [
            'custom unique id', 'data date', 'Kode TRX', 'No Transaksi', 'NIK',
            'Nama Petani', 'Urea', 'NPK', 'SP36', 'ZA', 'NPK Formula',
            'Organik', 'Organik Cair', 'Keterangan', 'Tanggal Tebus',
            'Url Bukti', 'value', 'color', 'color2', 'color3', 'color4',
            'color5', 'color6', 'color7'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sampleData = [
            [1, '2025-05-08', '34624069', 'S0D131\K0015M', '3506050104550004',
             'ABDULLAH ZAINI YAHYA', 0, 390, 0, 0, 0, 0, 0,
             'Sample', 21, 'https://example.com', 0,
             'black', 'green', 'black', 'black', 'black', 'black', 'black']
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ];
        $sheet->getStyle('A1:X1')->applyFromArray($headerStyle);

        // Auto width
        foreach (range('A', 'X') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

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
        $this->authorize('delete', $dataset);

        $dataset->delete();
        return redirect()->route('datasets.index')->with('success', 'Dataset berhasil dihapus!');
    }
}
