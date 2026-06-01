<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Earthquakes</title>
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
                    <h2>Recent Seismic Activity</h2>
                </header>
                
                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by country or region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <!-- Map Container -->
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid #ccc; z-index: 1;"></div>

                <section id="earthquakes-table-container" class="table-responsive">
                    <p>Loading earthquake data...</p>
                </section>
            </article>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('earthquakes-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            // Initialize OpenStreetMap via Leaflet
            const map = L.map('map').setView([20, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            let markersLayer = L.layerGroup().addTo(map);

            const fetchEarthquakes = async (country = '') => {
                try {
                    let url = '/api/earthquakes';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                    updateMap(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Failed to load seismic data. Please try again later.</p>';
                }
            };

            const updateMap = (earthquakes) => {
                markersLayer.clearLayers();
                if (!earthquakes || earthquakes.length === 0) return;

                const bounds = [];
                earthquakes.forEach(eq => {
                    if (eq.latitude && eq.longitude) {
                        const lat = parseFloat(eq.latitude);
                        const lng = parseFloat(eq.longitude);
                        const marker = L.marker([lat, lng]);
                        marker.bindPopup(`
                            <strong>Earthquake in ${eq.region}</strong><br>
                            Magnitude: ${eq.magnitude} ${eq.magnitude_type}<br>
                            Depth: ${eq.depth} km<br>
                            Time: ${new Date(eq.event_time).toLocaleString()}
                        `);
                        markersLayer.addLayer(marker);
                        bounds.push([lat, lng]);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 6 });
                }
            };

            const renderTable = (earthquakes) => {
                if (!earthquakes || earthquakes.length === 0) {
                    tableContainer.innerHTML = '<p>No earthquake records found.</p>';
                    return;
                }

                let html = `
                    <table>
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Region</th>
                                <th>Magnitude</th>
                                <th>Depth (km)</th>
                                <th>Authority</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                earthquakes.forEach(eq => {
                    const eventDate = new Date(eq.event_time).toLocaleString();
                    html += `
                        <tr>
                            <td>${eventDate}</td>
                            <td>${eq.region}</td>
                            <td><strong>${eq.magnitude}</strong> ${eq.magnitude_type}</td>
                            <td>${eq.depth}</td>
                            <td>${eq.authority}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                tableContainer.innerHTML = html;
            };

            searchBtn.addEventListener('click', () => fetchEarthquakes(searchInput.value));
            searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchEarthquakes(searchInput.value); });
            csvBtn.addEventListener('click', () => {
                let url = '/api/csv?type=earthquake';
                if (searchInput.value) url += '&country=' + encodeURIComponent(searchInput.value);
                window.location.href = url;
            });

            fetchEarthquakes();
        });
    </script>
</body>
</html>