<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
</head>
<body>
    <main class="container">
        <header>
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <nav>
                <a href="/" class="btn">Dashboard</a>
            </nav>
        </header>

        <div>
            <article class="card">
                <header>
                    <h2>Active Wildfire Detections</h2>
                </header>

                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <!-- Map Container -->
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid #ccc; z-index: 1;"></div>

                <section id="fires-table-container" class="table-responsive">
                    <p>Fetching latest fire data...</p>
                </section>
            </article>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('fires-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            // Initialize OpenStreetMap via Leaflet
            const map = L.map('map').setView([20, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var markersLayer = L.layerGroup().addTo(map);

            const fetchFires = async (country = '') => {
                try {
                    var url = '/api/fires';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                    updateMap(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Unable to load wildfire reports.</p>';
                }
            };

            const updateMap = (fires) => {
                markersLayer.clearLayers();
                if (!fires || fires.length === 0) return;

                const bounds = [];
                const fireIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                fires.forEach(f => {
                    if (f.latitude && f.longitude) {
                        const lat = parseFloat(f.latitude);
                        const lng = parseFloat(f.longitude);
                        const marker = L.marker([lat, lng], {icon: fireIcon});
                        marker.bindPopup(`
                            <strong>${f.title}</strong><br>
                            Time: ${new Date(f.event_time).toLocaleString()}
                        `);
                        markersLayer.addLayer(marker);
                        bounds.push([lat, lng]);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 6 });
                }
            };

            const renderTable = (fires) => {
                if (!fires || fires.length === 0) {
                    tableContainer.innerHTML = '<p>No active fire records found.</p>';
                    return;
                }

                const table = document.createElement('table');
                table.innerHTML = '<thead><tr><th>Detection Time</th><th>Event Details</th><th>Location</th></tr></thead>';
                const tbody = document.createElement('tbody');

                fires.forEach(f => {
                    const row = document.createElement('tr');
                    
                    const cell1 = document.createElement('td');
                    cell1.textContent = new Date(f.event_time).toLocaleString();
                    row.appendChild(cell1);

                    const cell2 = document.createElement('td');
                    cell2.textContent = f.title;
                    row.appendChild(cell2);

                    const cell3 = document.createElement('td');
                    cell3.textContent = `Lat: ${f.latitude}, Lng: ${f.longitude}`;
                    row.appendChild(cell3);
                    
                    tbody.appendChild(row);
                });
                
                table.appendChild(tbody);
                tableContainer.innerHTML = '';
                tableContainer.appendChild(table);
            };

            searchBtn.addEventListener('click', () => fetchFires(searchInput.value));
            searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchFires(searchInput.value); });
            csvBtn.addEventListener('click', () => {
                var url = '/api/csv?type=fire';
                if (searchInput.value) url += '&country=' + encodeURIComponent(searchInput.value);
                window.location.href = url;
            });

            fetchFires();
        });
    </script>
</body>
</html>