<?php

namespace App\Http\Controllers;

use App\Models\FertilizerTransaction;
use App\Models\BoundaryLayer;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function index()
    {
        $datasets = Dataset::where('is_active', true)
            ->where('import_status', 'completed')
            ->get();

        $boundaryLayers = BoundaryLayer::where('is_active', true)->get();

        return view('pages.maps.index', compact('datasets', 'boundaryLayers'));
    }

    public function getTransactionPoints(Request $request)
    {
        $query = FertilizerTransaction::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->dataset_id) {
            $query->where('dataset_id', $request->dataset_id);
        }

        if ($request->year) {
            $query->whereYear('transaction_date', $request->year);
        }

        if ($request->month) {
            $query->whereMonth('transaction_date', $request->month);
        }

        $transactions = $query->get()->map(function ($t) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$t->longitude, (float)$t->latitude]
                ],
                'properties' => [
                    'id' => $t->id,
                    'farmer_name' => $t->farmer_name,
                    'nik' => $t->nik,
                    'transaction_code' => $t->transaction_code,
                    'transaction_date' => $t->transaction_date->format('Y-m-d'),
                    'urea' => $t->urea,
                    'npk' => $t->npk,
                    'sp36' => $t->sp36,
                    'za' => $t->za,
                    'total' => $t->total_fertilizer,
                    'urea_color' => $t->urea_color,
                    'npk_color' => $t->npk_color,
                    'sp36_color' => $t->sp36_color,
                    'za_color' => $t->za_color,
                    'address' => $t->address,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $transactions
        ]);
    }

    public function getBoundaries(Request $request)
    {
        $boundaries = BoundaryLayer::where('is_active', true);

        if ($request->type) {
            $boundaries->where('type', $request->type);
        }

        $data = $boundaries->get()->map(function ($b) {
            $geojson = is_string($b->geojson) ? json_decode($b->geojson, true) : $b->geojson;

            return [
                'type' => 'Feature',
                'geometry' => $geojson,
                'properties' => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'code' => $b->code,
                    'type' => $b->type,
                    'fillColor' => $b->fill_color,
                    'borderColor' => $b->border_color,
                    'opacity' => $b->opacity,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $data
        ]);
    }

    public function getStatistics(Request $request)
    {
        $query = FertilizerTransaction::query();

        if ($request->dataset_id) {
            $query->where('dataset_id', $request->dataset_id);
        }

        if ($request->year) {
            $query->whereYear('transaction_date', $request->year);
        }

        if ($request->month) {
            $query->whereMonth('transaction_date', $request->month);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_farmers' => $query->distinct('nik')->count(),
            'total_urea' => $query->sum('urea'),
            'total_npk' => $query->sum('npk'),
            'total_sp36' => $query->sum('sp36'),
            'total_za' => $query->sum('za'),
            'total_all' => $query->sum(DB::raw('urea + npk + sp36 + za + npk_formula + organic + organic_liquid')),
        ];

        return response()->json($stats);
    }
}
