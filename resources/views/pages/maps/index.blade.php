@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    #map { height: 100vh; width: 100%; }
    .leaflet-popup-content { min-width: 300px; }
    .map-controls {
        position: absolute;
        top: 80px;
        left: 20px;
        z-index: 1000;
        max-width: 350px;
    }
    .stats-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        z-index: 1000;
        display: none;
    }
    .layer-panel {
        position: absolute;
        top: 80px;
        right: 20px;
        z-index: 1000;
        max-width: 300px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    .fertilizer-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        margin: 2px;
    }
    .z-99999 { z-index: 99999; }
    .h-9\.5 { height: 2.375rem; }
    .w-9\.5 { width: 2.375rem; }
</style>
@endpush
@section('content')
<div class="relative">
    <div id="map"></div>

    {{-- Top Controls --}}
    <div class="map-controls">
        <div class="bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-xl p-4 space-y-3">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <h3 class="text-white font-semibold">Map Controls</h3>
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">Select Dataset</label>
                <select id="datasetFilter" class="w-full bg-gray-900 text-white px-3 py-2 rounded text-sm">
                    <option value="">All Datasets</option>
                    @foreach($datasets as $dataset)
                        <option value="{{ $dataset->id }}">{{ $dataset->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Year</label>
                    <select id="yearFilter" class="w-full bg-gray-900 text-white px-3 py-2 rounded text-sm">
                        <option value="">All</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Month</label>
                    <select id="monthFilter" class="w-full bg-gray-900 text-white px-3 py-2 rounded text-sm">
                        <option value="">All</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-700">
                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                    <input type="checkbox" id="clusterToggle" checked class="rounded">
                    <span>Enable Clustering</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer mt-2">
                    <input type="checkbox" id="boundaryToggle" checked class="rounded">
                    <span>Show Boundaries</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer mt-2">
                    <input type="checkbox" id="heatmapToggle" class="rounded">
                    <span>Heatmap Mode</span>
                </label>
            </div>

            <button id="refreshBtn" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition">
                Refresh Data
            </button>
        </div>
    </div>

    {{-- Layer Panel --}}
    <div class="layer-panel">
        <div class="bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-xl p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Layers
                </h3>
                <button id="toggleLayerPanel" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-2" id="layersList">
                <div class="bg-gray-900 rounded p-3">
                    <label class="flex items-center gap-2 text-sm text-white cursor-pointer">
                        <input type="checkbox" class="layer-toggle" data-layer="boundaries" checked>
                        <span>Administrative Boundaries</span>
                    </label>
                </div>
                <div class="bg-gray-900 rounded p-3">
                    <label class="flex items-center gap-2 text-sm text-white cursor-pointer">
                        <input type="checkbox" class="layer-toggle" data-layer="points" checked>
                        <span>Transaction Points</span>
                    </label>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700">
                <h4 class="text-white text-sm font-semibold mb-3">Legend</h4>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                        <span class="text-gray-300">High Usage (>500kg)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                        <span class="text-gray-300">Medium (200-500kg)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                        <span class="text-gray-300">Low (<200kg)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Panel --}}
    <div class="stats-panel" id="statsPanel">
        <div class="bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-xl p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">Total Transactions</div>
                    <div class="text-2xl font-bold text-white" id="statTotalTransactions">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">Total Farmers</div>
                    <div class="text-2xl font-bold text-white" id="statTotalFarmers">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">Urea (kg)</div>
                    <div class="text-2xl font-bold text-blue-400" id="statUrea">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">NPK (kg)</div>
                    <div class="text-2xl font-bold text-green-400" id="statNPK">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">SP36 (kg)</div>
                    <div class="text-2xl font-bold text-yellow-400" id="statSP36">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">ZA (kg)</div>
                    <div class="text-2xl font-bold text-purple-400" id="statZA">-</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-400 mb-1">Total (kg)</div>
                    <div class="text-2xl font-bold text-red-400" id="statTotal">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail Modal - TailAdmin Style --}}
<div id="detailModal" class="hidden fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-900/50 backdrop-blur-[32px]" onclick="closeDetailModal()"></div>
    <div class="relative w-full max-w-[684px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
        {{-- Close Button --}}
        <button onclick="closeDetailModal()" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-900 text-gray-500 transition-colors hover:bg-gray-900 hover:text-gray-500 dark:bg-gray-900 dark:hover:bg-gray-900 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
            </svg>
        </button>

        {{-- Modal Content --}}
        <div id="modalContent">
            <h4 class="mb-6 text-xl font-medium text-gray-800 dark:text-white/90">
                Transaction Details
            </h4>

            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Farmer Name
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-farmer-name">-</div>
                </div>

                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        NIK
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-nik">-</div>
                </div>

                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Transaction Code
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-transaction-code">-</div>
                </div>

                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Transaction Date
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-date">-</div>
                </div>

                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Phone
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-phone">-</div>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Address
                    </label>
                    <div class="min-h-[44px] w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-address">-</div>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Fertilizer Distribution
                    </label>
                    <div class="w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="grid grid-cols-2 gap-3" id="detail-fertilizers">
                            <!-- Fertilizers will be populated here -->
                        </div>
                    </div>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Coordinates
                    </label>
                    <div class="h-11 w-full rounded-lg border border-gray-300 bg-gray-90 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" id="detail-coordinates">-</div>
                </div>
            </div>

            <div class="flex items-center justify-end w-full gap-3 mt-6">
                <button onclick="closeDetailModal()" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-90 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                    Close
                </button>
                <button type="button" onclick="viewOnMap()" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-500 shadow-theme-xs hover:bg-blue-600 sm:w-auto">
                    View on Map
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
let map, markersLayer, boundariesLayer, clusterGroup;
let currentFilters = {
    dataset_id: '',
    year: '',
    month: ''
};
let currentMarkerData = null;

// Initialize Map
function initMap() {
    map = L.map('map', {
        center: [-7.2575, 112.7521],
        zoom: 10,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    clusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });

    markersLayer = L.layerGroup();
    boundariesLayer = L.layerGroup().addTo(map);

    loadBoundaries();
    loadTransactionPoints();
    loadStatistics();
}

// Load Boundaries
function loadBoundaries() {
    fetch("{{ route('maps.api.boundaries') }}")
        .then(res => res.json())
        .then(data => {
            boundariesLayer.clearLayers();

            L.geoJSON(data, {
                style: feature => ({
                    fillColor: feature.properties.fillColor || '#3388ff',
                    weight: 2,
                    opacity: 1,
                    color: feature.properties.borderColor || '#ffffff',
                    fillOpacity: feature.properties.opacity || 0.5
                }),
                onEachFeature: (feature, layer) => {
                    layer.bindPopup(`
                        <div class="p-2">
                            <h3 class="font-bold text-lg">${feature.properties.name}</h3>
                            <p class="text-sm text-gray-600">Code: ${feature.properties.code}</p>
                            <p class="text-sm text-gray-600">Type: ${feature.properties.type}</p>
                        </div>
                    `);
                }
            }).addTo(boundariesLayer);
        });
}

// Load Transaction Points
function loadTransactionPoints() {
    const params = new URLSearchParams(currentFilters);

    fetch("{{ route('maps.api.points') }}?" + params)
        .then(res => res.json())
        .then(data => {
            markersLayer.clearLayers();
            clusterGroup.clearLayers();

            data.features.forEach(feature => {
                const props = feature.properties;
                const coords = feature.geometry.coordinates;

                // Determine marker color based on total
                let color = 'green';
                if (props.total > 500) color = 'red';
                else if (props.total > 200) color = 'yellow';

                const markerIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background:${color};width:20px;height:20px;border-radius:50%;border:2px solid white;"></div>`,
                    iconSize: [20, 20]
                });

                const marker = L.marker([coords[1], coords[0]], { icon: markerIcon });

                // Store props in marker for later use
                marker.markerData = props;

                // Add click event to open modal
                marker.on('click', () => {
                    openDetailModal(props);
                });

                // Create simple popup
                const popupContent = `
                    <div class="p-3">
                        <h3 class="font-bold text-lg mb-2">${props.farmer_name}</h3>
                        <div class="text-sm space-y-1">
                            <p><strong>NIK:</strong> ${props.nik}</p>
                            <p><strong>Transaction:</strong> ${props.transaction_code}</p>
                            <p><strong>Total:</strong> ${props.total} kg</p>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent);

                if (document.getElementById('clusterToggle').checked) {
                    clusterGroup.addLayer(marker);
                } else {
                    markersLayer.addLayer(marker);
                }
            });

            if (document.getElementById('clusterToggle').checked) {
                map.addLayer(clusterGroup);
            } else {
                map.addLayer(markersLayer);
            }
        })
        .catch(err => console.error('Error loading transaction points:', err));
}

// Load Statistics
function loadStatistics() {
    const params = new URLSearchParams(currentFilters);

    fetch("{{ route('maps.api.statistics') }}?" + params)
        .then(res => res.json())
        .then(data => {
            document.getElementById('statTotalTransactions').textContent = data.total_transactions || 0;
            document.getElementById('statTotalFarmers').textContent = data.total_farmers || 0;
            document.getElementById('statUrea').textContent = data.total_urea || 0;
            document.getElementById('statNPK').textContent = data.total_npk || 0;
            document.getElementById('statSP36').textContent = data.total_sp36 || 0;
            document.getElementById('statZA').textContent = data.total_za || 0;
            document.getElementById('statTotal').textContent = data.total_all || 0;

            document.getElementById('statsPanel').style.display = 'block';
        })
        .catch(err => console.error('Error loading statistics:', err));
}

// Open Detail Modal
function openDetailModal(props) {
    currentMarkerData = props;

    document.getElementById('detail-farmer-name').textContent = props.farmer_name || '-';
    document.getElementById('detail-nik').textContent = props.nik || '-';
    document.getElementById('detail-transaction-code').textContent = props.transaction_code || '-';
    document.getElementById('detail-date').textContent = props.transaction_date || '-';
    document.getElementById('detail-phone').textContent = props.phone || '-';
    document.getElementById('detail-address').textContent = props.address || '-';
    document.getElementById('detail-coordinates').textContent = props.latitude && props.longitude ? `${props.latitude}, ${props.longitude}` : '-';

    // Populate fertilizers
    const fertilizersHtml = [];
    if (props.urea > 0) {
        fertilizersHtml.push(`
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                <div class="text-xs text-gray-600 dark:text-gray-400">Urea</div>
                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">${props.urea} kg</div>
            </div>
        `);
    }
    if (props.npk > 0) {
        fertilizersHtml.push(`
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                <div class="text-xs text-gray-600 dark:text-gray-400">NPK</div>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">${props.npk} kg</div>
            </div>
        `);
    }
    if (props.sp36 > 0) {
        fertilizersHtml.push(`
            <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-3">
                <div class="text-xs text-gray-600 dark:text-gray-400">SP36</div>
                <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">${props.sp36} kg</div>
            </div>
        `);
    }
    if (props.za > 0) {
        fertilizersHtml.push(`
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3">
                <div class="text-xs text-gray-600 dark:text-gray-400">ZA</div>
                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">${props.za} kg</div>
            </div>
        `);
    }

    document.getElementById('detail-fertilizers').innerHTML = fertilizersHtml.length > 0
        ? fertilizersHtml.join('')
        : '<p class="text-sm text-gray-500 col-span-2 text-center py-2">No fertilizer data</p>';

    document.getElementById('detailModal').classList.remove('hidden');
}

// Close Detail Modal
function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
    currentMarkerData = null;
}

// View on Map
function viewOnMap() {
    if (currentMarkerData && currentMarkerData.latitude && currentMarkerData.longitude) {
        map.setView([currentMarkerData.latitude, currentMarkerData.longitude], 16);
        closeDetailModal();
    }
}

// Event Listeners
document.getElementById('datasetFilter').addEventListener('change', (e) => {
    currentFilters.dataset_id = e.target.value;
    loadTransactionPoints();
    loadStatistics();
});

document.getElementById('yearFilter').addEventListener('change', (e) => {
    currentFilters.year = e.target.value;
    loadTransactionPoints();
    loadStatistics();
});

document.getElementById('monthFilter').addEventListener('change', (e) => {
    currentFilters.month = e.target.value;
    loadTransactionPoints();
    loadStatistics();
});

document.getElementById('clusterToggle').addEventListener('change', (e) => {
    map.removeLayer(clusterGroup);
    map.removeLayer(markersLayer);
    loadTransactionPoints();
});

document.getElementById('boundaryToggle').addEventListener('change', (e) => {
    if (e.target.checked) {
        map.addLayer(boundariesLayer);
    } else {
        map.removeLayer(boundariesLayer);
    }
});

document.getElementById('refreshBtn').addEventListener('click', () => {
    loadTransactionPoints();
    loadStatistics();
});

document.getElementById('toggleLayerPanel').addEventListener('click', () => {
    const panel = document.querySelector('.layer-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
});

// Layer toggles
document.querySelectorAll('.layer-toggle').forEach(toggle => {
    toggle.addEventListener('change', (e) => {
        const layer = e.target.dataset.layer;
        if (layer === 'boundaries') {
            if (e.target.checked) {
                map.addLayer(boundariesLayer);
            } else {
                map.removeLayer(boundariesLayer);
            }
        } else if (layer === 'points') {
            if (e.target.checked) {
                if (document.getElementById('clusterToggle').checked) {
                    map.addLayer(clusterGroup);
                } else {
                    map.addLayer(markersLayer);
                }
            } else {
                map.removeLayer(clusterGroup);
                map.removeLayer(markersLayer);
            }
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
