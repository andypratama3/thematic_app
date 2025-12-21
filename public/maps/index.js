// ============================================
// FIXED LEAFLET MAP SCRIPT
// ============================================

(function () {
    'use strict';

    let map, markersLayer, boundariesLayer, clusterGroup;
    let currentFilters = {
        dataset_id: '',
        year: '',
        month: ''
    };

    // Helper function to safely get element
    function getElement(id) {
        return document.getElementById(id);
    }

    // Helper function to safely get element value
    function getElementValue(id) {
        const el = getElement(id);
        return el ? el.value : '';
    }

    // Helper function to safely check checkbox
    function isChecked(id) {
        const el = getElement(id);
        return el ? el.checked : false;
    }

    // Initialize Map
    function initMap() {
        const mapContainer = getElement('map');

        if (!mapContainer) {
            console.error('Map container not found');
            return;
        }

        map = L.map('map', {
            center: [-7.2575, 112.7521],
            zoom: 10,
            zoomControl: true
        });

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
            className: 'map-tiles'
        }).addTo(map);

        // Initialize cluster group
        clusterGroup = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 80
        });

        // Initialize layers
        markersLayer = L.layerGroup();
        boundariesLayer = L.layerGroup();

        // Add boundaries by default if checkbox is checked
        if (isChecked('boundaryToggle')) {
            boundariesLayer.addTo(map);
        }

        // Load initial data
        loadBoundaries();
        loadTransactionPoints();
        loadStatistics();

        // Setup event listeners
        setupEventListeners();
    }

    // Load Boundaries
    function loadBoundaries() {
        const boundariesUrl = "{{ route('maps.api.boundaries') }}";

        fetch(boundariesUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                boundariesLayer.clearLayers();

                if (!data || !data.features) {
                    console.warn('No boundary data received');
                    return;
                }

                L.geoJSON(data, {
                    style: (feature) => ({
                        fillColor: feature.properties?.fillColor || '#3388ff',
                        weight: 2,
                        opacity: 1,
                        color: feature.properties?.borderColor || '#ffffff',
                        fillOpacity: feature.properties?.opacity || 0.5
                    }),
                    onEachFeature: (feature, layer) => {
                        const props = feature.properties || {};
                        const popupContent = `
                            <div class="p-2 rounded">
                                <h3 class="font-bold text-lg text-gray-900">${props.name || 'Unknown'}</h3>
                                <p class="text-sm text-gray-600">Code: ${props.code || '-'}</p>
                                <p class="text-sm text-gray-600">Type: ${props.type || '-'}</p>
                            </div>
                        `;
                        layer.bindPopup(popupContent);
                    }
                }).addTo(boundariesLayer);
            })
            .catch(error => {
                console.error('Error loading boundaries:', error);
            });
    }

    // Load Transaction Points
    function loadTransactionPoints() {
        const params = new URLSearchParams(currentFilters);
        const pointsUrl = "{{ route('maps.api.points') }}?" + params.toString();

        fetch(pointsUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                markersLayer.clearLayers();
                clusterGroup.clearLayers();

                if (!data || !data.features) {
                    console.warn('No transaction data received');
                    return;
                }

                data.features.forEach(feature => {
                    const props = feature.properties || {};
                    const coords = feature.geometry?.coordinates;

                    if (!coords) return;

                    // Determine marker color based on total
                    let color = 'green';
                    const total = props.total || 0;
                    if (total > 500) {
                        color = 'red';
                    } else if (total > 200) {
                        color = 'yellow';
                    }

                    // Create marker icon
                    const markerIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background:${color};width:20px;height:20px;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    // Create marker
                    const marker = L.marker([coords[1], coords[0]], {
                        icon: markerIcon
                    });

                    // Create detailed popup
                    const fertilizerHtml = [
                        props.urea > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.urea_color || 'black')}">Urea: ${props.urea} kg</span>` : '',
                        props.npk > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.npk_color || 'black')}">NPK: ${props.npk} kg</span>` : '',
                        props.sp36 > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.sp36_color || 'black')}">SP36: ${props.sp36} kg</span>` : '',
                        props.za > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.za_color || 'black')}">ZA: ${props.za} kg</span>` : ''
                    ].filter(Boolean).join('');

                    const popupContent = `
                        <div class="p-3 rounded">
                            <h3 class="font-bold text-lg mb-2 text-gray-900">${props.farmer_name || 'Unknown'}</h3>
                            <div class="text-sm space-y-1 text-gray-700">
                                <p><strong>NIK:</strong> ${props.nik || '-'}</p>
                                <p><strong>Transaction:</strong> ${props.transaction_code || '-'}</p>
                                <p><strong>Date:</strong> ${props.transaction_date || '-'}</p>
                                <p><strong>Address:</strong> ${props.address || '-'}</p>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-300">
                                <p class="font-semibold mb-2 text-gray-900">Fertilizer Distribution:</p>
                                <div class="space-y-1 flex flex-wrap gap-1">
                                    ${fertilizerHtml || '<span class="text-gray-500">No data</span>'}
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-300">
                                    <strong class="text-gray-900">Total: ${total} kg</strong>
                                </div>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent);

                    // Add to appropriate layer
                    if (isChecked('clusterToggle')) {
                        clusterGroup.addLayer(marker);
                    } else {
                        markersLayer.addLayer(marker);
                    }
                });

                // Add layers to map
                if (isChecked('clusterToggle')) {
                    if (map.hasLayer(markersLayer)) {
                        map.removeLayer(markersLayer);
                    }
                    map.addLayer(clusterGroup);
                } else {
                    if (map.hasLayer(clusterGroup)) {
                        map.removeLayer(clusterGroup);
                    }
                    markersLayer.addTo(map);
                }

                // Show statistics panel
                const statsPanel = getElement('statsPanel');
                if (statsPanel) {
                    statsPanel.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading transaction points:', error);
            });
    }

    // Get color for fertilizer type
    function getColorForType(color) {
        const colors = {
            'red': '#ef4444',
            'green': '#10b981',
            'yellow': '#f59e0b',
            'blue': '#3b82f6',
            'purple': '#a855f7',
            'black': '#1f2937'
        };
        return colors[color] || colors['black'];
    }

    // Load Statistics
    function loadStatistics() {
        const params = new URLSearchParams(currentFilters);
        const statsUrl = "{{ route('maps.api.statistics') }}?" + params.toString();

        fetch(statsUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(stats => {
                const formatNumber = (num) => {
                    return (num || 0).toLocaleString('id-ID');
                };

                const elements = {
                    'statTotalTransactions': stats.total_transactions,
                    'statTotalFarmers': stats.total_farmers,
                    'statUrea': stats.total_urea,
                    'statNPK': stats.total_npk,
                    'statSP36': stats.total_sp36,
                    'statZA': stats.total_za,
                    'statTotal': stats.total_all
                };

                Object.keys(elements).forEach(id => {
                    const el = getElement(id);
                    if (el) {
                        el.textContent = formatNumber(elements[id]);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    }

    // Setup event listeners
    function setupEventListeners() {
        // Dataset filter
        const datasetFilter = getElement('datasetFilter');
        if (datasetFilter) {
            datasetFilter.addEventListener('change', (e) => {
                currentFilters.dataset_id = e.target.value;
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Year filter
        const yearFilter = getElement('yearFilter');
        if (yearFilter) {
            yearFilter.addEventListener('change', (e) => {
                currentFilters.year = e.target.value;
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Month filter
        const monthFilter = getElement('monthFilter');
        if (monthFilter) {
            monthFilter.addEventListener('change', (e) => {
                currentFilters.month = e.target.value;
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Cluster toggle
        const clusterToggle = getElement('clusterToggle');
        if (clusterToggle) {
            clusterToggle.addEventListener('change', () => {
                loadTransactionPoints();
            });
        }

        // Boundary toggle
        const boundaryToggle = getElement('boundaryToggle');
        if (boundaryToggle) {
            boundaryToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    map.addLayer(boundariesLayer);
                } else {
                    map.removeLayer(boundariesLayer);
                }
            });
        }

        // Refresh button
        const refreshBtn = getElement('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                loadBoundaries();
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Heatmap toggle (if exists)
        const heatmapToggle = getElement('heatmapToggle');
        if (heatmapToggle) {
            heatmapToggle.addEventListener('change', (e) => {
                console.log('Heatmap toggled:', e.target.checked);
                // Implement heatmap functionality here if needed
            });
        }
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        try {
            initMap();
        } catch (error) {
            console.error('Error initializing map:', error);
        }
    });

    // Handle window resize for map
    window.addEventListener('resize', () => {
        if (map) {
            map.invalidateSize();
        }
    });

})();
