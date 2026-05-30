<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <header class="dashboard-header">
        <h1><?= APP_NAME ?></h1>
        <p id="sync-indicator" class="sync-status">Checking for updates...</p>
    </header>

    <div class="container">
        <nav class="navigation-menu">
<<<<<<< HEAD
            <a href="/floods" class="btn btn-nav">Floods</a>
            <a href="/earthquakes" class="btn btn-nav">Earthquakes</a>
            <a href="/fires" class="btn btn-nav">Fires</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/admin/shelters" class="btn btn-nav" style="border-color: #28a745; color: #28a745;">Manage Shelters</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="btn btn-nav" style="border-color: #dc3545; color: #dc3545;">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a>
            <?php else: ?>
                <a href="/login" class="btn btn-nav">Login</a>
=======
            <a href="/floods" class="btn">Floods</a>
            <a href="/earthquakes" class="btn">Earthquakes</a>
            <a href="/fires" class="btn">Fires</a>
            <a href="/report" class="btn">Report</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="btn btn-logout">Logout</a>
            <?php else: ?>
                <a href="/login" class="btn">Login</a>
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
            <?php endif; ?>
        </nav>

        <main class="dashboard-grid">
            <article class="card">
                <header>
                    <h2>Latest Floods</h2>
                </header>
                <div id="floods-container" class="table-responsive">Loading...</div>
            </article>

            <article class="card">
                <header>
                    <h2>Latest Fires</h2>
                </header>
                <div id="fires-container" class="table-responsive">Loading...</div>
            </article>

            <article class="card">
                <header>
                    <h2>Latest Earthquakes</h2>
                </header>
                <div id="earthquakes-container" class="table-responsive">Loading...</div>
            </article>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusLabel = document.getElementById('sync-indicator');

            const loadLocalData = async () => {
                try {
                    const response = await fetch('/api/disasters');
                    const data = await response.json();
                    
                    renderTable('floods-container', data.floods, 'Flood');
                    renderTable('fires-container', data.fires, 'Fire');
                    renderTable('earthquakes-container', data.earthquakes, 'Earthquake', true);
                } catch (error) {
                    console.error('Data error:', error);
                }
            };

            const autoSync = async () => {
                statusLabel.textContent = '🔄 Syncing with authorities...';
                statusLabel.classList.add('syncing');

                try {
                    const response = await fetch('/api/sync');
                    const result = await response.json();
                    statusLabel.textContent = '✅ System up to date';
                    loadLocalData();
                } catch (error) {
                    statusLabel.textContent = '⚠️ Sync delayed';
                } finally {
                    statusLabel.classList.remove('syncing');
                    setTimeout(autoSync, 300000); 
                }
            };

            const renderTable = (containerId, items, type, isEarthquake = false) => {
                const container = document.getElementById(containerId);
                if (!items || items.length === 0) {
                    container.innerHTML = `<p>No recent ${type.toLowerCase()} activity.</p>`;
                    return;
                }

                let html = '<table><thead><tr>';
                html += isEarthquake ? '<th>Region</th><th>Mag</th>' : '<th>Title</th>';
                html += '<th>Date</th></tr></thead><tbody>';

                items.slice(0, 5).forEach(item => {
                    html += `<tr>`;
                    if (isEarthquake) {
                        html += `<td>${item.region}</td><td>${item.magnitude}</td>`;
                    } else {
                        html += `<td>${item.title.substring(0, 30)}...</td>`;
                    }
                    html += `<td>${new Date(item.event_time).toLocaleTimeString()}</td></tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            };

            loadLocalData();
            autoSync();
        });
    </script>
</body>
</html>
