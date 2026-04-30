<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <!-- 
        We use relative paths for CSS. 
        Note: In a real environment, you might use an absolute URL or a helper.
    -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?= APP_NAME ?></h1>
            <p>Disaster Management Dashboard</p>
        </header>

        <section class="actions">
            <!-- Button to trigger manual synchronization -->
            <button id="sync-btn" class="btn">Sync GDACS Data</button>
            <span id="sync-status"></span>
        </section>

        <main class="dashboard-grid">
            <!-- Containers for our disaster tables -->
            <div class="card">
                <h2>Latest Floods</h2>
                <div id="floods-container">Loading floods...</div>
            </div>

            <div class="card">
                <h2>Latest Fires</h2>
                <div id="fires-container">Loading fires...</div>
            </div>

            <div class="card">
                <h2>Latest Earthquakes</h2>
                <div id="earthquakes-container">Loading earthquakes...</div>
            </div>
        </main>
    </div>

    <!-- 
        Frontend Logic (Vanilla JS)
        Handles asynchronous communication with the PHP backend.
    -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const syncBtn = document.getElementById('sync-btn');
            const statusLabel = document.getElementById('sync-status');

            /**
             * Fetches disaster data from our PHP API
             */
            const loadData = async () => {
                try {
                    const response = await fetch('index.php?action=api_data');
                    const data = await response.json();
                    
                    renderTable('floods-container', data.floods, 'Flood');
                    renderTable('fires-container', data.fires, 'Fire');
                    renderTable('earthquakes-container', data.earthquakes, 'Earthquake', true);
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            };

            /**
             * Renders a simple HTML table for the provided data
             */
            const renderTable = (containerId, items, type, isEarthquake = false) => {
                const container = document.getElementById(containerId);
                if (!items || items.length === 0) {
                    container.innerHTML = `<p>No ${type.toLowerCase()} records found.</p>`;
                    return;
                }

                let html = '<table><thead><tr>';
                html += isEarthquake ? '<th>Region</th><th>Mag</th><th>Date</th>' : '<th>Title</th><th>Date</th>';
                html += '</tr></thead><tbody>';

                items.forEach(item => {
                    html += '<tr>';
                    if (isEarthquake) {
                        html += `<td>${item.region}</td><td>${item.magnitude}</td>`;
                    } else {
                        html += `<td>${item.title}</td>`;
                    }
                    html += `<td>${new Date(item.event_time).toLocaleString()}</td></tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            };

            /**
             * Trigger Sync Button Logic
             */
            syncBtn.addEventListener('click', async () => {
                syncBtn.disabled = true;
                statusLabel.textContent = 'Synchronizing...';
                
                try {
                    const response = await fetch('index.php?action=sync');
                    const result = await response.json();
                    
                    statusLabel.textContent = result.message;
                    // Reload data after successful sync
                    loadData();
                } catch (error) {
                    statusLabel.textContent = 'Sync failed. Check console.';
                    console.error('Sync Error:', error);
                } finally {
                    syncBtn.disabled = false;
                }
            });

            // Initial load
            loadData();
        });
    </script>
</body>
</html>
