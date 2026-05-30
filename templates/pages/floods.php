<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Floods</title>
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
                    <h2>Active Flood Alerts</h2>
                </header>

                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <section id="floods-table-container" class="table-responsive">
                    <p>Fetching data from authorities...</p>
                </section>
            </article>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('floods-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            const fetchFloods = async (country = '') => {
                try {
                    let url = '/api/floods';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Unable to load flood alerts.</p>';
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
