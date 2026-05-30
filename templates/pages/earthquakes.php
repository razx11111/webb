<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Earthquakes</title>
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
                    <h2>Recent Seismic Activity</h2>
                </header>
                
                <section class="controls-bar">
                    <input type="text" id="country-search" class="search-field" placeholder="Search by country or region...">
                    <button id="search-btn" class="btn btn-search">Search</button>
                    <button id="csv-btn" class="btn btn-csv">Download CSV</button>
                </section>

                <section id="earthquakes-table-container" class="table-responsive">
                    <p>Loading earthquake data...</p>
                </section>
            </article>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('earthquakes-table-container');
            const searchInput = document.getElementById('country-search');
            const searchBtn = document.getElementById('search-btn');
            const csvBtn = document.getElementById('csv-btn');

            const fetchEarthquakes = async (country = '') => {
                try {
                    let url = '/api/earthquakes';
                    if (country) url += '?country=' + encodeURIComponent(country);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('API failure');
                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Failed to load seismic data. Please try again later.</p>';
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
