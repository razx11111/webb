<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Fires</title>
    <link rel="stylesheet" href="/css/style.css">
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
                    <h2>Active Wildfire Detections</h2>
                </header>

                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <section id="fires-table-container" class="table-responsive">
                    <p>Fetching latest fire data...</p>
                </section>
            </article>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('fires-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            const fetchFires = async (country = '') => {
                try {
                    let url = '/api/fires';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Unable to load wildfire reports.</p>';
                }
            };

            const renderTable = (fires) => {
                if (!fires || fires.length === 0) {
                    tableContainer.innerHTML = '<p>No active fire records found.</p>';
                    return;
                }

                let html = '<table><thead><tr><th>Detection Time</th><th>Event Details</th><th>Location</th></tr></thead><tbody>';
                fires.forEach(f => {
                    const date = new Date(f.event_time).toLocaleString();
                    html += `<tr><td>${date}</td><td>${f.title}</td><td>Lat: ${f.latitude}, Lng: ${f.longitude}</td></tr>`;
                });
                html += '</tbody></table>';
                tableContainer.innerHTML = html;
            };

            searchBtn.addEventListener('click', () => fetchFires(searchInput.value));
            searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchFires(searchInput.value); });
            csvBtn.addEventListener('click', () => {
                let url = '/api/csv?type=fire';
                if (searchInput.value) url += '&country=' + encodeURIComponent(searchInput.value);
                window.location.href = url;
            });

            fetchFires();
        });
    </script>
</body>
</html>
