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

            const fetchFloods = async () => {
                try {
                    // Fetching from the specific clean API endpoint
                    const response = await fetch('/api/floods');
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

            fetchFloods();
        });
    </script>
</body>
</html>
