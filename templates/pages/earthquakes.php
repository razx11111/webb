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

        <div class="card-container">
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
        </div>
    </main>

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

            var markersLayer = L.layerGroup().addTo(map);

            // Simple function to escape HTML characters from a string.
            const escapeHTML = (str) => {
                if (str === null || str === undefined) return '';
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            };

            const fetchEarthquakes = async (country = '') => {
                try {
                    var url = '/api/earthquakes';
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

                        // Sanitize all data before putting it into the HTML string for the popup.
                        const safeRegion = escapeHTML(eq.region);
                        const safeMag = escapeHTML(eq.magnitude);
                        const safeMagType = escapeHTML(eq.magnitude_type);
                        const safeDepth = escapeHTML(eq.depth);
                        const safeTime = escapeHTML(new Date(eq.event_time).toLocaleString());

                        marker.bindPopup(`
                            <strong>Earthquake in ${safeRegion}</strong><br>
                            Magnitude: ${safeMag} ${safeMagType}<br>
                            Depth: ${safeDepth} km<br>
                            Time: ${safeTime}
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

                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const tbody = document.createElement('tbody');
                
                // Define headers
                const headers = ['Date & Time', 'Region', 'Magnitude', 'Depth (km)', 'Authority'];
                const headerRow = document.createElement('tr');
                headers.forEach(headerText => {
                    const th = document.createElement('th');
                    th.textContent = headerText;
                    headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);

                // Create and append rows. Using .textContent is crucial for security.
                earthquakes.forEach(eq => {
                    const row = document.createElement('tr');
                    
                    const eventDate = new Date(eq.event_time).toLocaleString();
                    const rowData = [
                        eventDate,
                        eq.region,
                        `${eq.magnitude} ${eq.magnitude_type}`,
                        eq.depth,
                        eq.authority
                    ];

                    rowData.forEach(cellData => {
                        const cell = document.createElement('td');
                        cell.textContent = cellData;
                        // Add strong tag for magnitude for styling
                        if (cellData.includes(eq.magnitude)) {
                            const strong = document.createElement('strong');
                            strong.textContent = eq.magnitude;
                            cell.innerHTML = ''; // Clear text content
                            cell.appendChild(strong);
                            cell.append(` ${eq.magnitude_type}`);
                        }
                        row.appendChild(cell);
                    });

                    tbody.appendChild(row);
                });

                table.appendChild(thead);
                table.appendChild(tbody);
                
                // Replace container content safely
                tableContainer.innerHTML = '';
                tableContainer.appendChild(table);
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