<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Floods</title>
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
                <h2>Active Flood Alerts</h2>
                <div style="margin-bottom: 15px;">
                    <input type="text" id="country-search" placeholder="Search by country or region..." style="padding: 8px; width: 300px;">
                    <button id="search-btn" class="btn">Search</button>
                    <button id="csv-btn" class="btn" style="background-color: #28a745; margin-left: 10px;">Download CSV</button>
                </div>
                <div id="floods-table-container">
                    <p>Fetching data from authorities...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        /**
         * AJAX Implementation for Flood Data
         */
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('floods-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            const fetchFloods = async (country = '') => {
                try {
                    let url = '/api/floods';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    // Fetching from the specific clean API endpoint
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Failed to reach server');

                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">System error: Unable to retrieve flood data.</p>';
                }
            };

            const renderTable = (floods) => {
                if (!floods || floods.length === 0) {
                    tableContainer.innerHTML = '<p>No flood records currently reported.</p>';
                    return;
                }

                let html = `
                    <table>
                        <thead>
                            <tr>
                                <th>Report Time</th>
                                <th>Title / Description</th>
                                <th>Coordinates</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                floods.forEach(f => {
                    // Date parsing and localization
                    const date = new Date(f.event_time).toLocaleString();
                    html += `
                        <tr>
                            <td>${date}</td>
                            <td>${f.title}</td>
                            <td>Lat: ${f.latitude}, Lng: ${f.longitude}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                tableContainer.innerHTML = html;
            };

            searchBtn.addEventListener('click', () => {
                fetchFloods(searchInput.value);
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') fetchFloods(searchInput.value);
            });

            csvBtn.addEventListener('click', () => {
                let url = '/api/csv?type=flood';
                if (searchInput.value) {
                    url += '&country=' + encodeURIComponent(searchInput.value);
                }
                window.location.href = url;
            });

            fetchFloods();
        });
    </script>
</body>
</html>
