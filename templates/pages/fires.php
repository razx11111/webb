<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Fires</title>
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
                <h2>Active Wildfire Detections</h2>
                <div id="fires-table-container">
                    <p>Connecting to NASA FIRMS / GDACS...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        /**
         * AJAX Implementation for Fire Data
         */
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('fires-table-container');

            const fetchFires = async () => {
                try {
                    // Requesting JSON data from the clean API route
                    const response = await fetch('/api/fires');
                    if (!response.ok) throw new Error('API unreachable');

                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = '<p class="error">Offline: Could not load fire reports.</p>';
                }
            };

            const renderTable = (fires) => {
                if (!fires || fires.length === 0) {
                    tableContainer.innerHTML = '<p>No active fires found in the vicinity.</p>';
                    return;
                }

                let html = `
                    <table>
                        <thead>
                            <tr>
                                <th>Detection Time</th>
                                <th>Event Details</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                fires.forEach(f => {
                    // Converting ISO timestamp to local user format
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

            fetchFires();
        });
    </script>
</body>
</html>
