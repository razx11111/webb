<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Earthquakes</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/home.css">
    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $pageTitle ?></h1>
            <a href="/" class="btn">Back to Dashboard</a>
        </header>

        <main>
            <div class="card">
                <h2>Recent Seismic Activity</h2>
                <div style="margin-bottom: 15px;">
                    <input type="text" id="country-search" placeholder="Search by country or region..." style="padding: 8px; width: 300px;">
                    <button id="search-btn" class="btn">Search</button>
                    <button id="csv-btn" class="btn" style="background-color: #28a745; margin-left: 10px;">Download CSV</button>
                </div>
                
                <!-- Map Container -->
                <div id="map" style="height: 400px; margin-bottom: 20px; border: 1px solid #ccc; z-index: 1;"></div>

                <div id="earthquakes-table-container">
                    <p>Loading earthquake data...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        /**
         * AJAX Implementation for Earthquake Data
         * This script runs once the page is loaded.
         */
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('earthquakes-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            // Initialize OpenStreetMap via Leaflet
            // Set default view to a global perspective
            const map = L.map('map').setView([20, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Keep track of markers so we can clear them when searching
            let markersLayer = L.layerGroup().addTo(map);

            /**
             * Fetch data from the Clean API Endpoint (/api/earthquakes)
             */
            const fetchEarthquakes = async (country = '') => {
                try {
                    let url = '/api/earthquakes';
                    if (country) {
                        url += '?country=' + encodeURIComponent(country);
                    }
                    const response = await fetch(url);
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    renderTable(data);
                    updateMap(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Failed to load data. Please try again later.</p>';
                }
            };

            const updateMap = (earthquakes) => {
                // Clear old markers from the map
                markersLayer.clearLayers();

                if (!earthquakes || earthquakes.length === 0) return;

                const bounds = [];

                earthquakes.forEach(eq => {
                    if (eq.latitude && eq.longitude) {
                        const lat = parseFloat(eq.latitude);
                        const lng = parseFloat(eq.longitude);
                        
                        // Add marker with a popup containing details
                        const marker = L.marker([lat, lng]);
                        marker.bindPopup(`
                            <strong>Earthquake in ${eq.region}</strong><br>
                            Magnitude: ${eq.magnitude} ${eq.magnitude_type}<br>
                            Depth: ${eq.depth} km<br>
                            Time: ${new Date(eq.event_time).toLocaleString()}
                        `);
                        markersLayer.addLayer(marker);

                        // Save coordinates to calculate map zoom later
                        bounds.push([lat, lng]);
                    }
                });

                // Automatically zoom and pan the map to fit all new markers perfectly!
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 6 });
                }
            };

            /**
             * Renders the JSON data into a clean HTML table
             */
            const renderTable = (earthquakes) => {
                if (!earthquakes || earthquakes.length === 0) {
                    tableContainer.innerHTML = '<p>No earthquake records found in the database.</p>';
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
                    // Converting the TIMESTAMP from DB to a local readable format
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

            searchBtn.addEventListener('click', () => {
                fetchEarthquakes(searchInput.value);
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    fetchEarthquakes(searchInput.value);
                }
            });

            csvBtn.addEventListener('click', () => {
                let url = '/api/csv?type=earthquake';
                if (searchInput.value) {
                    url += '&country=' + encodeURIComponent(searchInput.value);
                }
                window.location.href = url;
            });

            // Trigger the initial data fetch
            fetchEarthquakes();
        });
    </script>
</body>
</html>
