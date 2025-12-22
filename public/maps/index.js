// ============================================
// LEAFLET HEATMAP IMPLEMENTATION
// ============================================

(function () {
    'use strict';

    let map, markersLayer, boundariesLayer, clusterGroup, heatmapLayer;
    let heatmapData = [];
    let currentFilters = {
        dataset_id: '',
        year: '',
        month: ''
    };

    // Helper function to safely get element
    function getElement(id) {
        return document.getElementById(id);
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

        console.log('🗺️ Initializing map...');

        map = L.map('map', {
            center: [-7.8167, 112.0167],
            zoom: 10,
            zoomControl: true,
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

        // Initialize heatmap layer (hidden by default)
        heatmapLayer = L.heatLayer([], {
            radius: 25,
            blur: 15,
            maxZoom: 18,
            gradient: {
                0.0: '#0000ff',    // Blue - Low intensity
                0.25: '#00ff00',   // Green - Low-Medium
                0.5: '#ffff00',    // Yellow - Medium
                0.75: '#ff8800',   // Orange - Medium-High
                1.0: '#ff0000'     // Red - High intensity
            }
        });

        if (isChecked('boundaryToggle')) {
            boundariesLayer.addTo(map);
        }

        // Load initial data
        loadBoundaries();
        loadTransactionPoints();
        loadStatistics();
        setupEventListeners();

        console.log('✅ Map initialized successfully');
    }

    // Load Boundaries
    function loadBoundaries() {
        const boundariesUrl = "/maps/api/boundaries";

        console.log('📍 Loading boundaries from:', boundariesUrl);

        fetch(boundariesUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log('📍 Boundaries loaded:', data);

                boundariesLayer.clearLayers();

                if (!data || !data.features || data.features.length === 0) {
                    console.warn('⚠️ No boundary data received');
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
                                <h3 class="font-bold text-lg text-white">${props.name || 'Unknown'}</h3>
                                <p class="text-sm text-gray-300">Code: ${props.code || '-'}</p>
                                <p class="text-sm text-gray-300">Type: ${props.type || '-'}</p>
                            </div>
                        `;
                        layer.bindPopup(popupContent);
                    }
                }).addTo(boundariesLayer);

                console.log('✅ Boundaries added to map');
            })
            .catch(error => {
                console.error('❌ Error loading boundaries:', error);
            });
    }

    // Load Transaction Points & Prepare Heatmap Data
    function loadTransactionPoints() {
        const params = new URLSearchParams(currentFilters);
        const pointsUrl = "/maps/api/points?" + params.toString();

        console.log('🎯 Loading transaction points from:', pointsUrl);
        console.log('🎯 Filters:', currentFilters);

        fetch(pointsUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log('🎯 Transaction points loaded:', data);
                console.log('🎯 Total features:', data.features?.length || 0);

                markersLayer.clearLayers();
                clusterGroup.clearLayers();
                heatmapData = [];

                if (!data || !data.features || data.features.length === 0) {
                    console.warn('⚠️ No transaction data received');
                    alert('⚠️ Tidak ada data transaksi dengan koordinat!\n\nPastikan data Excel Anda memiliki koordinat atau alamat yang valid.');
                    return;
                }

                let validMarkers = 0;
                let invalidMarkers = 0;
                let maxTotal = 0;

                // First pass: find max value for normalization
                data.features.forEach(feature => {
                    const props = feature.properties || {};
                    const total = props.total || 0;
                    if (total > maxTotal) {
                        maxTotal = total;
                    }
                });

                // Second pass: create markers and heatmap data
                data.features.forEach(feature => {
                    const props = feature.properties || {};
                    const coords = feature.geometry?.coordinates;

                    if (!coords || !Array.isArray(coords) || coords.length < 2) {
                        console.warn('⚠️ Invalid coordinates for feature:', feature);
                        invalidMarkers++;
                        return;
                    }

                    if (!props.farmer_name && !props.nik) {
                        console.warn('⚠️ Skipping empty data:', props);
                        invalidMarkers++;
                        return;
                    }

                    const total = props.total || 0;
                    const lat = coords[1];
                    const lng = coords[0];

                    // ========== PREPARE HEATMAP DATA ==========
                    // Format: [lat, lng, intensity (0-1)]
                    const intensity = maxTotal > 0 ? total / maxTotal : 0;
                    heatmapData.push([lat, lng, intensity]);

                    // ========== CREATE MARKERS ==========
                    let color = 'green';
                    if (total > 500) {
                        color = 'red';
                    } else if (total > 200) {
                        color = 'yellow';
                    }

                    const markerIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background:${color};width:20px;height:20px;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    const marker = L.marker([lat, lng], {
                        icon: markerIcon
                    });

                    const fertilizerHtml = [
                        props.urea > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.urea_color || 'black')}">Urea: ${props.urea} kg</span>` : '',
                        props.npk > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.npk_color || 'black')}">NPK: ${props.npk} kg</span>` : '',
                        props.sp36 > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.sp36_color || 'black')}">SP36: ${props.sp36} kg</span>` : '',
                        props.za > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.za_color || 'black')}">ZA: ${props.za} kg</span>` : ''
                    ].filter(Boolean).join('');

                    const popupContent = `
                        <div class="p-3 rounded">
                            <h3 class="font-bold text-lg mb-2 text-white">${props.farmer_name || 'Data Kosong'}</h3>
                            <div class="text-sm space-y-1 text-gray-300">
                                <p><strong>NIK:</strong> ${props.nik || '-'}</p>
                                <p><strong>Transaction:</strong> ${props.transaction_code || '-'}</p>
                                <p><strong>Date:</strong> ${props.transaction_date || '-'}</p>
                                <p><strong>Address:</strong> ${props.address || '-'}</p>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-600">
                                <p class="font-semibold mb-2 text-white">Distribusi Pupuk:</p>
                                <div class="space-y-1 flex flex-wrap gap-1">
                                    ${fertilizerHtml || '<span class="text-gray-400">Tidak ada data pupuk</span>'}
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-600">
                                    <strong class="text-white">Total: ${total} kg</strong>
                                </div>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent);

                    if (isChecked('clusterToggle')) {
                        clusterGroup.addLayer(marker);
                    } else {
                        markersLayer.addLayer(marker);
                    }

                    validMarkers++;
                });

                console.log(`✅ Added ${validMarkers} valid markers`);
                console.log(`⚠️ Skipped ${invalidMarkers} invalid markers`);
                console.log(`🔥 Heatmap data prepared: ${heatmapData.length} points`);

                // Update heatmap if active
                if (isChecked('heatmapToggle')) {
                    updateHeatmap();
                }

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

                // Auto-fit bounds to markers
                if (validMarkers > 0) {
                    const group = isChecked('clusterToggle') ? clusterGroup : markersLayer;
                    if (group.getLayers().length > 0) {
                        map.fitBounds(group.getBounds(), { padding: [50, 50] });
                    }
                }

                const statsPanel = getElement('statsPanel');
                if (statsPanel) {
                    statsPanel.style.display = 'block';
                }

                if (validMarkers > 0) {
                    console.log(`🎉 Successfully loaded ${validMarkers} points!`);
                }
            })
            .catch(error => {
                console.error('❌ Error loading transaction points:', error);
                alert('❌ Error loading data: ' + error.message);
            });
    }

    // Update Heatmap Layer
    function updateHeatmap() {
        console.log('🔥 Updating heatmap with', heatmapData.length, 'data points');

        if (heatmapData.length === 0) {
            console.warn('⚠️ No heatmap data available');
            return;
        }

        // Update heatmap data
        heatmapLayer.setLatLngs(heatmapData);

        // Auto-fit bounds if heatmap is visible
        if (map.hasLayer(heatmapLayer) && heatmapData.length > 0) {
            const bounds = heatmapData.map(point => [point[0], point[1]]);
            const group = L.featureGroup(bounds.map(b => L.marker(b)));
            if (bounds.length > 0) {
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            }
        }

        console.log('✅ Heatmap updated successfully');
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
        const statsUrl = "/maps/api/statistics?" + params.toString();

        console.log('📊 Loading statistics from:', statsUrl);

        fetch(statsUrl)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(stats => {
                console.log('📊 Statistics loaded:', stats);

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

                console.log('✅ Statistics updated');
            })
            .catch(error => {
                console.error('❌ Error loading statistics:', error);
            });
    }

    // Setup event listeners
    function setupEventListeners() {
        // Dataset filter
        const datasetFilter = getElement('datasetFilter');
        if (datasetFilter) {
            datasetFilter.addEventListener('change', (e) => {
                currentFilters.dataset_id = e.target.value;
                console.log('🔄 Dataset filter changed:', e.target.value);
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Year filter
        const yearFilter = getElement('yearFilter');
        if (yearFilter) {
            yearFilter.addEventListener('change', (e) => {
                currentFilters.year = e.target.value;
                console.log('🔄 Year filter changed:', e.target.value);
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Month filter
        const monthFilter = getElement('monthFilter');
        if (monthFilter) {
            monthFilter.addEventListener('change', (e) => {
                currentFilters.month = e.target.value;
                console.log('🔄 Month filter changed:', e.target.value);
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Cluster toggle
        const clusterToggle = getElement('clusterToggle');
        if (clusterToggle) {
            clusterToggle.addEventListener('change', () => {
                console.log('🔄 Cluster toggle changed:', clusterToggle.checked);
                loadTransactionPoints();
            });
        }

        // Boundary toggle
        const boundaryToggle = getElement('boundaryToggle');
        if (boundaryToggle) {
            boundaryToggle.addEventListener('change', (e) => {
                console.log('🔄 Boundary toggle changed:', e.target.checked);
                if (e.target.checked) {
                    map.addLayer(boundariesLayer);
                } else {
                    map.removeLayer(boundariesLayer);
                }
            });
        }

        // ========== HEATMAP TOGGLE ==========
        const heatmapToggle = getElement('heatmapToggle');
        if (heatmapToggle) {
            heatmapToggle.addEventListener('change', (e) => {
                console.log('🔥 Heatmap toggle changed:', e.target.checked);

                if (e.target.checked) {
                    // Enable heatmap mode
                    if (heatmapData.length === 0) {
                        alert('⚠️ Tidak ada data untuk heatmap. Silakan muat data terlebih dahulu.');
                        e.target.checked = false;
                        return;
                    }

                    // Hide markers and clusters
                    if (map.hasLayer(markersLayer)) {
                        map.removeLayer(markersLayer);
                    }
                    if (map.hasLayer(clusterGroup)) {
                        map.removeLayer(clusterGroup);
                    }

                    // Show heatmap
                    updateHeatmap();
                    map.addLayer(heatmapLayer);

                    console.log('🔥 Heatmap mode ON - Markers hidden');
                } else {
                    // Disable heatmap mode
                    if (map.hasLayer(heatmapLayer)) {
                        map.removeLayer(heatmapLayer);
                    }

                    // Show markers again
                    if (isChecked('clusterToggle')) {
                        map.addLayer(clusterGroup);
                    } else {
                        map.addLayer(markersLayer);
                    }

                    console.log('🔥 Heatmap mode OFF - Markers visible');
                }
            });
        }

        // Refresh button
        const refreshBtn = getElement('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('🔄 Refreshing all data...');
                loadBoundaries();
                loadTransactionPoints();
                loadStatistics();
            });
        }

        // Close/Toggle Layer Panel
        const toggleLayerPanel = getElement('toggleLayerPanel');
        const layerPanel = document.querySelector('.layer-panel');

        if (toggleLayerPanel && layerPanel) {
            toggleLayerPanel.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = layerPanel.style.display === 'none';
                layerPanel.style.display = isHidden ? 'block' : 'none';
                toggleLayerPanel.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(90deg)';
            });
        }

        console.log('✅ Event listeners setup complete');
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🚀 DOM Content Loaded - Starting initialization...');
        try {
            initMap();
        } catch (error) {
            console.error('❌ Error initializing map:', error);
            alert('Error initializing map: ' + error.message);
        }
    });

    // Handle window resize for map
    window.addEventListener('resize', () => {
        if (map) {
            map.invalidateSize();
        }
    });

    // Debug function
    window.debugMap = function() {
        console.log('=== MAP DEBUG INFO ===');
        console.log('Map:', map);
        console.log('Heatmap Data:', heatmapData);
        console.log('Heatmap Layer Active:', map.hasLayer(heatmapLayer));
        console.log('Current Filters:', currentFilters);
    };

})();
