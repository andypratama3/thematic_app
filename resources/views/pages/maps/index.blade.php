@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    :root {
        --map-tiles-filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
        --primary-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
    }

    @media (prefers-color-scheme: dark) {
        .map-tiles {
            filter: var(--map-tiles-filter, none);
        }
    }

    #map {
        height: 100vh;
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
    }

    .leaflet-popup-content {
        min-width: 320px;
        font-size: 13px;
    }

    /* Map Controls Styling */
    .map-controls {
        position: absolute;
        top: 80px;
        left: 20px;
        z-index: 1000;
        max-width: 380px;
    }

    .control-panel {
        background: rgba(17, 24, 39, 0.95);
        backdrop-filter: blur(12px);
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .control-panel h3 {
        color: white;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .control-section {
        margin-bottom: 18px;
    }

    .control-section:last-child {
        margin-bottom: 0;
    }

    .control-section label {
        display: block;
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .control-section select,
    .control-section input {
        width: 100%;
        background: rgba(55, 65, 81, 0.8);
        color: white;
        border: 1px solid rgba(107, 114, 128, 0.5);
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .control-section select:hover,
    .control-section input:hover {
        border-color: rgba(107, 114, 128, 0.8);
        background: rgba(55, 65, 81, 1);
    }

    .control-section select:focus,
    .control-section input:focus {
        outline: none;
        border-color: var(--primary-color);
        background: rgba(55, 65, 81, 1);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .divider {
        height: 1px;
        background: rgba(107, 114, 128, 0.3);
        margin: 16px 0;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-color);
    }

    .checkbox-item label {
        margin: 0;
        color: #ffff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-transform: none;
        letter-spacing: normal;
    }

    /* Button Styling */
    #refreshBtn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, var(--primary-color), #2563eb);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 4px;
    }

    #refreshBtn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
    }

    #refreshBtn:active {
        transform: translateY(0);
    }

    /* Layer Panel */
    .layer-panel {
        position: absolute;
        top: 80px;
        right: 20px;
        z-index: 1000;
        max-width: 320px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }

    .layer-panel::-webkit-scrollbar {
        width: 6px;
    }

    .layer-panel::-webkit-scrollbar-track {
        background: transparent;
    }

    .layer-panel::-webkit-scrollbar-thumb {
        background: rgba(107, 114, 128, 0.5);
        border-radius: 3px;
    }

    .layer-panel::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.7);
    }

    .layer-item {
        background: rgba(55, 65, 81, 0.6);
        border: 1px solid rgba(107, 114, 128, 0.3);
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 10px;
        transition: all 0.2s;
    }

    .layer-item:hover {
        background: rgba(55, 65, 81, 0.8);
        border-color: rgba(107, 114, 128, 0.5);
    }

    .layer-item label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        margin: 0;
    }

    .layer-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-color);
    }

    /* Legend Styling */
    .legend {
        background: rgba(55, 65, 81, 0.6);
        border: 1px solid rgba(107, 114, 128, 0.3);
        border-radius: 8px;
        padding: 14px;
        margin-top: 12px;
    }

    .legend h4 {
        color: white;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #d1d5db;
    }

    .legend-item:last-child {
        margin-bottom: 0;
    }

    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Heatmap Legend */
    .heatmap-legend {
        background: rgba(55, 65, 81, 0.6);
        border: 1px solid rgba(107, 114, 128, 0.3);
        border-radius: 8px;
        padding: 14px;
        margin-top: 12px;
        display: none;
    }

    .heatmap-legend.active {
        display: block;
    }

    .heatmap-legend h4 {
        color: white;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .heatmap-gradient {
        width: 100%;
        height: 30px;
        background: linear-gradient(to right, #0000ff, #00ff00, #ffff00, #ff8800, #ff0000);
        border-radius: 4px;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .heatmap-labels {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #9ca3af;
        font-weight: 600;
    }

    /* Statistics Panel */
    .stats-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        z-index: 1000;
        display: none;
    }

    .stats-container {
        background: rgba(17, 24, 39, 0.95);
        backdrop-filter: blur(12px);
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 16px;
    }

    .stat-box {
        background: rgba(55, 65, 81, 0.5);
        border: 1px solid rgba(107, 114, 128, 0.3);
        border-radius: 8px;
        padding: 14px;
        text-align: center;
        transition: all 0.2s;
    }

    .stat-box:hover {
        background: rgba(55, 65, 81, 0.7);
        border-color: rgba(107, 114, 128, 0.5);
        transform: translateY(-2px);
    }

    .stat-label {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: white;
    }

    .stat-value.primary { color: #60a5fa; }
    .stat-value.success { color: #34d399; }
    .stat-value.warning { color: #fbbf24; }
    .stat-value.danger { color: #f87171; }
    .stat-value.purple { color: #a78bfa; }

    /* Fertilizer Badge */
    .fertilizer-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        margin: 4px 4px 4px 0;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Close Button */
    #toggleLayerPanel {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    #toggleLayerPanel:hover {
        color: white;
    }

    /* Leaflet Popup Custom Style */
    .leaflet-popup-content-wrapper {
        background: rgba(31, 41, 55, 0.95) !important;
        border-radius: 10px !important;
        border: 1px solid rgba(107, 114, 128, 0.3) !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    }

    .leaflet-popup-content {
        color: #e5e7eb !important;
    }

    .leaflet-popup-content h3 {
        color: white !important;
        font-size: 15px !important;
    }

    .leaflet-popup-content p {
        color: #d1d5db !important;
    }

    .leaflet-popup-content strong {
        color: white !important;
    }

    .leaflet-popup-tip {
        background: rgba(31, 41, 55, 0.95) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .map-controls {
            max-width: calc(100% - 40px);
            left: 20px;
        }

        .layer-panel {
            max-width: calc(100% - 40px);
            right: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        }

        .stat-value {
            font-size: 16px;
        }
    }
</style>
@endpush

@section('content')
<x-common.page-breadcrumb pageTitle="Map Monitoring" />
<div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
    <div class="mx-auto w-full">
        <div class="relative">
            {{-- Map Container --}}
            <div id="map"></div>

            {{-- Top Controls --}}
            <div class="map-controls">
                <div class="control-panel">
                    <h3>
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>Filter & Controls</span>
                    </h3>

                    <div class="control-section">
                        <label>Dataset</label>
                        <select id="datasetFilter">
                            <option value="">Semua Dataset</option>
                            @foreach($datasets as $dataset)
                                <option value="{{ $dataset->id }}">{{ $dataset->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="control-section">
                        <div class="grid-2">
                            <div>
                                <label>Tahun</label>
                                <select id="yearFilter">
                                    <option value="">Semua</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label>Bulan</label>
                                <select id="monthFilter">
                                    <option value="">Semua</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="control-section">
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="clusterToggle" checked>
                                <label for="clusterToggle">Kelompok Marker</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="boundaryToggle" checked>
                                <label for="boundaryToggle">Tampilkan Batas Wilayah</label>
                            </div>
                        </div>
                    </div>

                    <button id="refreshBtn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh Data
                    </button>
                </div>
            </div>

            {{-- Layer Panel --}}
            <div class="layer-panel">
                <div class="control-panel">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <h3 style="margin: 0;">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Layers
                        </h3>
                        <button id="toggleLayerPanel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div id="layersList">
                        <div class="layer-item">
                            <label>
                                <input type="checkbox" class="layer-toggle" data-layer="boundaries" checked>
                                <span class="text-white">Batas Administrasi</span>
                            </label>
                        </div>
                        <div class="layer-item">
                            <label>
                                <input type="checkbox" class="layer-toggle" data-layer="points" checked>
                                <span class="text-white">Titik Transaksi</span>
                            </label>
                        </div>
                    </div>

                    <div class="legend" id="markerLegend">
                        <h4>Legenda Marker</h4>
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #ef4444;"></div>
                            <span>Penggunaan Tinggi (>500kg)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #f59e0b;"></div>
                            <span>Menengah (200-500kg)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #10b981;"></div>
                            <span>Rendah (<200kg)</span>
                        </div>
                    </div>

                    <div class="heatmap-legend" id="heatmapLegend">
                        <h4>🔥 Legenda Heatmap</h4>
                        <div class="heatmap-gradient"></div>
                        <div class="heatmap-labels">
                            <span>Rendah</span>
                            <span>Sedang</span>
                            <span>Tinggi</span>
                        </div>
                        <p style="font-size: 11px; color: #9ca3af; margin-top: 8px;">
                            🔵 Biru = Konsentrasi rendah<br>
                            🟢 Hijau = Konsentrasi sedang<br>
                            🔴 Merah = Konsentrasi tinggi
                        </p>
                    </div>
                </div>
            </div>

            {{-- Statistics Panel --}}
            <div class="stats-panel" id="statsPanel">
                <div class="stats-container">
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-label">Total Transaksi</div>
                            <div class="stat-value primary" id="statTotalTransactions">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Total Petani</div>
                            <div class="stat-value success" id="statTotalFarmers">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Urea (kg)</div>
                            <div class="stat-value primary" id="statUrea">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">NPK (kg)</div>
                            <div class="stat-value success" id="statNPK">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">SP36 (kg)</div>
                            <div class="stat-value warning" id="statSP36">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">ZA (kg)</div>
                            <div class="stat-value purple" id="statZA">-</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Total (kg)</div>
                            <div class="stat-value danger" id="statTotal">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- ========== CRITICAL: Load in correct order ========== -->
<!-- 1. Leaflet core -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- 2. Marker cluster -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<!-- 3. HEATMAP LIBRARY (MUST be before index.js) -->
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<!-- 4. Your custom map code (LAST) -->
<script src="{{ asset('maps/index.js') }}"></script>

<!-- 5. Legend toggle script -->
<script>
    // Wait for DOM ready and heatmap library to load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if L.heatLayer is available
        if (typeof L === 'undefined' || typeof L.heatLayer === 'undefined') {
            console.warn('⚠️ Heatmap library not loaded properly');
        } else {
            console.log('✅ Heatmap library loaded successfully');
        }

        // Toggle heatmap legend visibility
        const heatmapToggle = document.getElementById('heatmapToggle');
        const markerLegend = document.getElementById('markerLegend');
        const heatmapLegend = document.getElementById('heatmapLegend');

        if (heatmapToggle) {
            heatmapToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    markerLegend.style.display = 'none';
                    heatmapLegend.classList.add('active');
                    console.log('🔥 Heatmap legend shown');
                } else {
                    markerLegend.style.display = 'block';
                    heatmapLegend.classList.remove('active');
                    console.log('📍 Marker legend shown');
                }
            });
        }
    });
</script>
@endpush
@endsection
