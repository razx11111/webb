<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/home.css?v=<?= time() ?>">

    <!-- Leaflet.js for Emergency Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Leaflet Routing Machine -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <style>
        /* FAIL-SAFE MODAL STYLES */
        #alert-overlay {
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

        #popup-map {
            height: 280px;
            width: 100%;
            margin-top: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            z-index: 10002;
            background: #f9f9f9;
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

    <dialog id="alert-overlay">
        <section id="emergency-popup">
            <header class="popup-header">
                <h2>🚨 Emergency Alert 🚨</h2>
            </header>
            <main class="popup-body">
                <p><strong>A danger has been detected near you!</strong></p>
                <p>Type: <span id="alert-type" style="color: #e53e3e; font-weight: bold;"></span></p>
                <p>Event: <span id="alert-name" style="font-style: italic;"></span></p>
                <aside class="shelter-info">
                    <h3 style="margin-top: 0;">Nearest Safe Shelter</h3>
                    <p id="nearest-shelter-name" style="font-weight: bold; font-size: 1.2rem; color: #2c3e50; margin: 5px 0;"></p>
                    <p id="shelter-distance" style="color: #4a5568; margin: 0;"></p>
                    <figure id="popup-map"></figure>
                </aside>
                <p>Please evacuate immediately.</p>
            </main>
            <footer class="popup-footer">
                <button onclick="dismissAlert()" class="btn btn-evacuate">Acknowledge & Dismiss</button>
            </footer>
        </section>
    </dialog>

    <header class="dashboard-header">
        <h1><?= APP_NAME ?></h1>
        <p id="sync-indicator" class="sync-status">Checking for updates...</p>
    </header>

    <section class="container">
        <nav class="navigation-menu">
            <a href="/floods" class="btn btn-nav">Floods</a>
            <a href="/earthquakes" class="btn btn-nav">Earthquakes</a>
            <a href="/fires" class="btn btn-nav">Fires</a>
            <a href="/report" class="btn btn-nav">Reports</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/admin/shelters" class="btn btn-nav" style="background-color: #28a745; color: white;">Manage Shelters</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="btn btn-logout">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a>
            <?php else: ?>
                <a href="/login" class="btn btn-nav">Login</a>
            <?php endif; ?>
        </nav>

        <section class="dashboard-grid">
            <article class="card">
                <header><h2 style="color: white;">Latest Floods</h2></header>
                <section id="floods-container" class="table-responsive">Loading...</section>
            </article>
            <article class="card">
                <header><h2 style="color: white;">Latest Fires</h2></header>
                <section id="fires-container" class="table-responsive">Loading...</section>
            </article>

            <article class="card">
                <header><h2 style="color: white;">Latest Earthquakes</h2></header>
                <section id="earthquakes-container" class="table-responsive">Loading...</section>
            </article>
        </section>
    </section>

    <script>
        function dismissAlert() {
            document.getElementById('alert-overlay').close();
            document.body.classList.remove('no-scroll');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const statusLabel = document.getElementById('sync-indicator');
            const overlay = document.getElementById('alert-overlay');
            const alertTypeDisplay = document.getElementById('alert-type');
            const alertNameDisplay = document.getElementById('alert-name');
            const shelterNameDisplay = document.getElementById('nearest-shelter-name');
            const shelterDistDisplay = document.getElementById('shelter-distance');

            var popupMap = null;
            var shelterMarker = null;
            var routingControl = null;

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

                        overlay.showModal();
                        document.body.classList.add('no-scroll');

                        // Map Initialization
                        if (result.shelter && result.shelter.lat) {
                            setTimeout(() => {
                                if (!popupMap) {
                                    popupMap = L.map('popup-map').setView([result.shelter.lat, result.shelter.lng], 14);
                                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(popupMap);
                                } else {
                                    popupMap.setView([result.shelter.lat, result.shelter.lng], 14);
                                }
                                
                                if (shelterMarker) popupMap.removeLayer(shelterMarker);
                                shelterMarker = L.marker([result.shelter.lat, result.shelter.lng]).addTo(popupMap)
                                    .bindPopup(`<b>${result.shelter.name}</b>`).openPopup();
                                
                                if (routingControl) {
                                    popupMap.removeControl(routingControl);
                                }
                                routingControl = L.routing.control({
                                    waypoints: [
                                        L.latLng(lat, lng),
                                        L.latLng(result.shelter.lat, result.shelter.lng)
                                    ],
                                    show: false
                                }).addTo(popupMap);

                                popupMap.invalidateSize();
                            }, 400);
                        }
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
                } catch (e) { console.error(e); if(statusLabel) statusLabel.textContent = ' Data offline'; }
            };

            const autoSync = async () => {
                try {
                    await fetch('/api/sync');
                    if(statusLabel) statusLabel.textContent = '✅ System Live';
                    loadData();
                } catch (e) { if(statusLabel) statusLabel.textContent = ' Sync suspended'; }
                finally { setTimeout(autoSync, 300000); }
            };

            const renderTable = (containerId, items, type, isEarthquake = false) => {
                const container = document.getElementById(containerId);
                if (!items || items.length === 0) {
                    container.innerHTML = `<p>No recent activity.</p>`;
                    return;
                }

                // Create table elements programmatically to prevent XSS.
                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const tbody = document.createElement('tbody');

                
                const headerRow = document.createElement('tr');
                const th1 = document.createElement('th');
                th1.textContent = isEarthquake ? 'Region' : 'Event';
                const th2 = document.createElement('th');
                th2.textContent = 'Time';
                headerRow.appendChild(th1);
                headerRow.appendChild(th2);
                thead.appendChild(headerRow);

                items.slice(0, 5).forEach(item => {
                    const row = document.createElement('tr');
                    const cell1 = document.createElement('td');
                    const cell2 = document.createElement('td');

                    // Use .textContent to safely insert data.
                    var title = isEarthquake ? item.region : item.title;
                    // Safely truncate the text
                    cell1.textContent = title.length > 25 ? title.substring(0, 25) + '...' : title;
                    cell2.textContent = new Date(item.event_time).toLocaleTimeString();
                    
                    row.appendChild(cell1);
                    row.appendChild(cell2);
                    tbody.appendChild(row);
                });

                table.appendChild(thead);
                table.appendChild(tbody);
                
                // Clear the container and append the new, safe table.
                container.innerHTML = '';
                container.appendChild(table);
            };

            loadData();
            autoSync();
        });
    </script>
</body>
</html>
