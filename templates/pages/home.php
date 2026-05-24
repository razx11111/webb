<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <!-- Using absolute paths for CSS consistency -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?= APP_NAME ?></h1>
            <div id="sync-indicator" class="sync-status">Checking for updates...</div>
        </header>

        <nav class="navigation-menu">
            <a href="/floods" class="btn">Floods</a>
            <a href="/earthquakes" class="btn">Earthquakes</a>
            <a href="/fires" class="btn">Fires</a>
            <a href="/report" class="btn">Report</a>
        </nav>

        <main class="dashboard-grid">
            <div class="card">
                <h2>Latest Floods</h2>
                <div id="floods-container">Loading...</div>
            </div>

            <div class="card">
                <h2>Latest Fires</h2>
                <div id="fires-container">Loading...</div>
            </div>

            <div class="card">
                <h2>Latest Earthquakes</h2>
                <div id="earthquakes-container">Loading...</div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusLabel = document.getElementById('sync-indicator');

            /**
             * Fetches data from our local database to show on UI
             */
            const loadLocalData = async () => {
                try {
                    const response = await fetch('/api/disasters');
                    const data = await response.json();
                    
                    renderTable('floods-container', data.floods, 'Flood');
                    renderTable('fires-container', data.fires, 'Fire');
                    renderTable('earthquakes-container', data.earthquakes, 'Earthquake', true);
                } catch (error) {
                    console.error('Data loading error:', error);
                }
            };

            /**
             * Automatically triggers the external data synchronization
             */
            const autoSync = async () => {
                statusLabel.textContent = '🔄 Syncing with authorities...';
                statusLabel.classList.add('syncing');

                try {
                    // Triggering the background sync process
                    const response = await fetch('/api/sync');
                    const result = await response.json();
                    
                    statusLabel.textContent = '✅ System up to date';
                    // Re-load the local data now that we have fresh records from sync
                    loadLocalData();
                } catch (error) {
                    statusLabel.textContent = '⚠️ Sync delayed';
                    console.error('Auto-sync failed:', error);
                } finally {
                    statusLabel.classList.remove('syncing');
                    // Optional: Set a timer to sync again in 5 minutes
                    setTimeout(autoSync, 300000); 
                }
            };

            /**
             * Helper to render UI tables
             */
            const renderTable = (containerId, items, type, isEarthquake = false) => {
                const container = document.getElementById(containerId);
                if (!items || items.length === 0) {
                    container.innerHTML = `<p>No recent ${type.toLowerCase()} activity.</p>`;
                    return;
                }

                let html = '<table><thead><tr>';
                html += isEarthquake ? '<th>Region</th><th>Mag</th>' : '<th>Title</th>';
                html += '<th>Date</th></tr></thead><tbody>';

                items.forEach(item => {
                    html += `<tr>`;
                    if (isEarthquake) {
                        html += `<td>${item.region}</td><td>${item.magnitude}</td>`;
                    } else {
                        html += `<td>${item.title}</td>`;
                    }
                    html += `<td>${new Date(item.event_time).toLocaleTimeString()}</td></tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            };

            // Initial steps: load what we have, then start the
            // auto-sync background process
            loadLocalData();
            autoSync();
        });
    </script>

    <style>
        .sync-status { font-size: 0.9rem; color: #666; margin-top: 5px; }
        .syncing { color: #007bff; font-weight: bold; }
    </style>
</body>
</html>
