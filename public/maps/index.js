 let map, markersLayer, boundariesLayer, clusterGroup;
 let currentFilters = {
     dataset_id: '',
     year: '',
     month: ''
 };

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

                 const marker = L.marker([coords[1], coords[0]], {
                     icon: markerIcon
                 });

                 // Create detailed popup
                 const popupContent = `
                        <div class="p-3">
                            <h3 class="font-bold text-lg mb-2">${props.farmer_name}</h3>
                            <div class="text-sm space-y-1">
                                <p><strong>NIK:</strong> ${props.nik}</p>
                                <p><strong>Transaction:</strong> ${props.transaction_code}</p>
                                <p><strong>Date:</strong> ${props.transaction_date}</p>
                                <p><strong>Address:</strong> ${props.address || '-'}</p>
                            </div>
                            <div class="mt-3 pt-3 border-t">
                                <p class="font-semibold mb-2">Fertilizer Distribution:</p>
                                <div class="space-y-1">
                                    ${props.urea > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.urea_color)}">Urea: ${props.urea} kg</span>` : ''}
                                    ${props.npk > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.npk_color)}">NPK: ${props.npk} kg</span>` : ''}
                                    ${props.sp36 > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.sp36_color)}">SP36: ${props.sp36} kg</span>` : ''}
                                    ${props.za > 0 ? `<span class="fertilizer-badge" style="background:${getColorForType(props.za_color)}">ZA: ${props.za} kg</span>` : ''}
                                </div>
                                <div class="mt-2 pt-2 border-t">
                                    <strong>Total: ${props.total} kg</strong>
                                </div>
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
                 markersLayer.addTo(map);
             }

             document.getElementById('statsPanel').style.display = 'block';
         });
 }

 // Get color for fertilizer type
 function getColorForType(color) {
     const colors = {
         'red': '#ef4444',
         'green': '#10b981',
         'yellow': '#f59e0b',
         'black': '#1f2937'
     };
     return colors[color] || colors['black'];
 }

 // Load Statistics
 function loadStatistics() {
     const params = new URLSearchParams(currentFilters);

     fetch("{{ route('maps.api.statistics') }}?" + params)
         .then(res => res.json())
         .then(stats => {
             document.getElementById('statTotalTransactions').textContent = stats.total_transactions.toLocaleString();
             document.getElementById('statTotalFarmers').textContent = stats.total_farmers.toLocaleString();
             document.getElementById('statUrea').textContent = stats.total_urea.toLocaleString();
             document.getElementById('statNPK').textContent = stats.total_npk.toLocaleString();
             document.getElementById('statSP36').textContent = stats.total_sp36.toLocaleString();
             document.getElementById('statZA').textContent = stats.total_za.toLocaleString();
             document.getElementById('statTotal').textContent = stats.total_all.toLocaleString();
         });
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

 document.getElementById('clusterToggle').addEventListener('change', () => {
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
     loadBoundaries();
     loadTransactionPoints();
     loadStatistics();
 });

 // Initialize on page load
 document.addEventListener('DOMContentLoaded', initMap);
