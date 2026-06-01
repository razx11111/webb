<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Floods</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $pageTitle ?></h1>
            <nav>
                <a href="/" class="btn">Dashboard</a>
            </nav>
        </header>

        <main>
            <article class="card">
                <header>
                    <h2>Active Flood Alerts</h2>
                </header>

                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <!-- Map Container -->
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid #ccc; z-index: 1;"></div>

                <section id="floods-table-container" class="table-responsive">
                    <p>Fetching data from authorities...</p>
                </section>
            </article>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('floods-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            // Initialize OpenStreetMap via Leaflet
            const map = L.map('map').setView([20, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            let markersLayer = L.layerGroup().addTo(map);

            const fetchFloods = async (country = '') => {
                try {
                    let url = '/api/floods';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                    updateMap(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Unable to load flood alerts.</p>';
                }
            };

            const updateMap = (floods) => {
                markersLayer.clearLayers();
                if (!floods || floods.length === 0) return;

                const bounds = [];
                // Use a custom icon for floods (blue marker)
                const floodIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                floods.forEach(f => {
                    if (f.latitude && f.longitude) {
                        const lat = parseFloat(f.latitude);
                        const lng = parseFloat(f.longitude);
                        
                        const marker = L.marker([lat, lng], {icon: floodIcon});
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

            const renderTable = (floods) => {
                if (!floods || floods.length === 0) {
                    tableContainer.innerHTML = '<p>No active flood reports found.</p>';
                    return;
                }

                let html = '<table><thead><tr><th>Report Time</th><th>Title / Description</th><th>Coordinates</th></tr></thead><tbody>';
                floods.forEach(f => {
                    const date = new Date(f.event_time).toLocaleString();
                    html += `<tr><td>${date}</td><td>${f.title}</td><td>Lat: ${f.latitude}, Lng: ${f.longitude}</td></tr>`;
                });
                html += '</tbody></table>';
                tableContainer.innerHTML = html;
            };

            searchBtn.addEventListener('click', () => fetchFloods(searchInput.value));
            searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchFloods(searchInput.value); });
            csvBtn.addEventListener('click', () => {
                let url = '/api/csv?type=flood';
                if (searchInput.value) url += '&country=' + encodeURIComponent(searchInput.value);
                window.location.href = url;
            });

            fetchFloods();
        });
    </script>
</body>
</html>