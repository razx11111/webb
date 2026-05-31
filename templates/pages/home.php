<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/home.css?v=<?= time() ?>">

    <style>
        /* FAIL-SAFE MODAL STYLES */
        #alert-overlay {
            display: none; 
            position: fixed !important;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background-color: rgba(0, 0, 0, 0.95) !important;
            z-index: 10000 !important;
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
        }

        #emergency-popup {
            float: left;
            display: block !important;
            width: 550px;
            max-width: 95%;
            background: #ffffff !important;
            border-radius: 12px;
            box-shadow: 0 0 100px rgba(255, 0, 0, 0.3);
            border: 6px solid #e53e3e !important;
            overflow: hidden;
            position: relative;
        }

        .popup-header {
            background: #e53e3e !important;
            color: white !important;
            padding: 20px;
            text-align: center;
        }

        .popup-header h2 {
            color: white !important;
            margin: 0 !important;
            font-size: 1.6rem;
            text-transform: uppercase;
        }

        .popup-body {
            padding: 30px;
            text-align: center;
            color: #1a202c !important;
            font-size: 1.15rem;
            line-height: 1.5;
        }

        .shelter-info {
            margin: 20px 0;
            padding: 20px;
            background: #fff5f5;
            border: 2px dashed #feb2b2;
            border-radius: 10px;
        }

        .popup-footer {
            padding: 20px;
            background: #f7fafc;
            text-align: center;
            border-top: 1px solid #edf2f7;
        }

        .btn-evacuate {
            display: inline-block;
            background: #e53e3e !important;
            color: white !important;
            padding: 15px 40px;
            font-size: 1.3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        body.no-scroll { overflow: hidden !important; }
    </style>
</head>
<body>

    <div id="alert-overlay">
        <section id="emergency-popup">
            <header class="popup-header">
                <h2>🚨 Emergency Alert 🚨</h2>
            </header>
            <div class="popup-body">
                <p><strong>A danger has been detected near you!</strong></p>
                <p>Type: <span id="alert-type" style="color: #e53e3e; font-weight: bold;"></span></p>
                <p>Event: <span id="alert-name" style="font-style: italic;"></span></p>
                <div class="shelter-info">
                    <h3 style="margin-top: 0;">Nearest Safe Shelter</h3>
                    <p id="nearest-shelter-name" style="font-weight: bold; font-size: 1.2rem; color: #2c3e50; margin: 5px 0;"></p>
                    <p id="shelter-distance" style="color: #4a5568; margin: 0;"></p>
                </div>
                <p>Please evacuate immediately.</p>
            </div>
            <footer class="popup-footer">
                <button onclick="dismissAlert()" class="btn btn-evacuate">Acknowledge & Dismiss</button>
            </footer>
        </section>
    </div>

    <header class="dashboard-header">
        <h1><?= APP_NAME ?></h1>
    </header>

    <div class="container">
        <nav class="navigation-menu">
            <a href="/floods" class="btn btn-nav">Floods</a>
            <a href="/earthquakes" class="btn btn-nav">Earthquakes</a>
            <a href="/fires" class="btn btn-nav">Fires</a>
            <a href="/report" class="btn btn-nav">Reports</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/admin/shelters" class="btn btn-nav" style="border-color: #28a745; color: #28a745;">Manage Shelters</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="btn btn-nav" style="border-color: #dc3545; color: #dc3545;">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a>
            <?php else: ?>
                <a href="/login" class="btn btn-nav">Login</a>
            <?php endif; ?>
        </nav>

        <main class="dashboard-grid">
            <article class="card">
                <header><h2 style="color: white;">Latest Floods</h2></header>
                <div id="floods-container" class="table-responsive">Loading...</div>
            </article>
            <article class="card">
                <header><h2 style="color: white;">Latest Fires</h2></header>
                <div id="fires-container" class="table-responsive">Loading...</div>
            </article>

            <article class="card">
                <header><h2 style="color: white;">Latest Earthquakes</h2></header>
                <div id="earthquakes-container" class="table-responsive">Loading...</div>
            </article>
        </main>
    </div>

    <script>
        function dismissAlert() {
            document.getElementById('alert-overlay').style.setProperty('display', 'none', 'important');
            document.body.classList.remove('no-scroll');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const statusLabel = document.getElementById('sync-indicator');
            const overlay = document.getElementById('alert-overlay');
            const alertTypeDisplay = document.getElementById('alert-type');
            const alertNameDisplay = document.getElementById('alert-name');
            const shelterNameDisplay = document.getElementById('nearest-shelter-name');
            const shelterDistDisplay = document.getElementById('shelter-distance');

            const checkSafetyStatus = async (lat, lng) => {
                try {
                    const response = await fetch(`/api/proximity-check?lat=${lat}&lng=${lng}`);
                    if (!response.ok) return;
                    const result = await response.json();
                    if (result.inDanger && result.details) {
                        alertTypeDisplay.textContent = result.details.type;
                        alertNameDisplay.textContent = result.details.name;
                        if (result.shelter) {
                            shelterNameDisplay.textContent = result.shelter.name;
                            shelterDistDisplay.textContent = `${result.shelter.distance} km away`;
                        }
                        overlay.style.setProperty('display', 'flex', 'important');
                        document.body.classList.add('no-scroll');
                    }
                } catch (e) { console.error(e); }
            };

            const loadData = async () => {
                try {
                    const response = await fetch('/api/disasters');
                    const data = await response.json();
                    renderTable('floods-container', data.floods, 'Flood');
                    renderTable('fires-container', data.fires, 'Fire');
                    renderTable('earthquakes-container', data.earthquakes, 'Earthquake', true);
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(p => {
                            checkSafetyStatus(p.coords.latitude, p.coords.longitude);
                        }, () => console.warn("Location denied"));
                    }
                } catch (e) { console.error(e); statusLabel.textContent = ' Data offline'; }
            };

            const autoSync = async () => {
                try {
                    await fetch('/api/sync');
                    loadData();
                } catch (e) { statusLabel.textContent = ' Sync suspended'; }
                finally { setTimeout(autoSync, 300000); }
            };

            const renderTable = (containerId, items, type, isEarthquake = false) => {
                const container = document.getElementById(containerId);
                if (!items || items.length === 0) {
                    container.innerHTML = `<p>No recent activity.</p>`;
                    return;
                }
                let html = '<table><thead><tr><th>' + (isEarthquake ? 'Region' : 'Event') + '</th><th>Time</th></tr></thead><tbody>';
                items.slice(0, 5).forEach(item => {
                    html += `<tr><td>${(isEarthquake ? item.region : item.title)}</td><td>${new Date(item.event_time).toLocaleTimeString()}</td></tr>`;
                });
                container.innerHTML = html + '</tbody></table>';
            };

            loadData();
            autoSync();
        });
    </script>
</body>
</html>
