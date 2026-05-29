<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Earthquakes</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/home.css">
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
                <div id="earthquakes-table-container">
                    <p>Loading earthquake data...</p>
                </div>
            </div>
        </main>
    </div>

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
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Failed to load data. Please try again later.</p>';
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
