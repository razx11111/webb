<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Manage Shelters</title>
    <link rel="stylesheet" href="/css/style.css">
    
    <!-- Leaflet.js Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        /* Essential layout for the map and split view */
        #map { height: 450px; width: 100%; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; z-index: 1; }
        .admin-grid { display: grid; grid-template-columns: 1fr 350px; gap: 20px; }
        .shelter-list-section { margin-top: 30px; }
        .shelter-form { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .coords-badge { background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 4px; font-family: monospace; display: block; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header class="dashboard-header" style="background-color: #2c3e50; color: white; padding: 1rem; text-align: center;">
        <h1>Manage Emergency Shelters</h1>
        <p>Current logged in as: <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></p>
    </header>

    <main class="container-fluid" style="padding: 20px;">
        <nav class="navigation-menu" style="margin-bottom: 20px;">
            <a href="/" class="btn btn-secondary">← Back to Dashboard</a>
        </nav>

        <main>
            <section class="admin-grid">
                <!-- Map View -->
                <section class="map-container">
                    <header>
                        <h2>1. Shelter Map</h2>
                        <p>Blue markers are existing shelters. Click on the map to place a NEW shelter (Red marker).</p>
                    </header>
                    <figure id="map"></figure>
                </section>

                <!-- Add Form -->
                <section class="form-container">
                    <header>
                        <h2>2. Shelter Details</h2>
                    </header>
                    
                    <?php if(isset($_GET['success'])): ?>
                        <strong class="alert-success">✅ Shelter added successfully!</strong>
                    <?php endif; ?>

                    <form action="/admin/shelters/add" method="POST" class="shelter-form">
                        <section class="form-group">
                            <label>Coordinates (Click map or type):</label>
                            <fieldset class="controls-bar" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                                <legend>Coordinates</legend>
                                <p>
                                    <small>Latitude</small>
                                    <input type="text" name="latitude" id="input-lat" class="form-control" placeholder="0.000000" required>
                                </p>
                                <p>
                                    <small>Longitude</small>
                                    <input type="text" name="longitude" id="input-lng" class="form-control" placeholder="0.000000" required>
                                </p>
                            </fieldset>
                        </section>

                        <section class="form-group">
                            <label for="name">Shelter Name:</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. North Hall Shelter" required>
                        </section>

                        <section class="form-group">
                            <label for="capacity">Estimated Capacity:</label>
                            <input type="number" name="capacity" id="capacity" class="form-control" placeholder="Max persons">
                        </section>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Save Shelter</button>
                    </form>
                </section>
            </section>

            <!-- Descriptive List -->
            <section class="shelter-list-section card">
                <header>
                    <h2>Existing Shelters Registry</h2>
                </header>
                <section class="table-responsive">
                    <table id="shelters-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Coordinates</th>
                                <th>Capacity</th>
                                <th>Added On</th>
                            </tr>
                        </thead>
                        <tbody id="shelters-list-body">
                            <tr><td colspan="4">Loading shelters...</td></tr>
                        </tbody>
                    </table>
                </section>
            </section>
        </main>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Setup map centered on a general area
            const map = L.map('map').setView([47.15, 27.60], 12);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Special marker for the "New Selection"
            const newMarkerIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            const selectionMarker = L.marker([0, 0], { icon: newMarkerIcon });
            
            const latInput = document.getElementById('input-lat');
            const lngInput = document.getElementById('input-lng');
            const tableBody = document.getElementById('shelters-list-body');

            // Updates the marker based on input values
            const updateMarkerFromInputs = () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);

                if (!isNaN(lat) && !isNaN(lng)) {
                    const latlng = L.latLng(lat, lng);
                    selectionMarker.setLatLng(latlng).addTo(map);
                    selectionMarker.bindPopup("<strong>Manually Entered Location</strong>").openPopup();
                }
            };

            // Listen for manual typing
            latInput.addEventListener('input', updateMarkerFromInputs);
            lngInput.addEventListener('input', updateMarkerFromInputs);

            // 1. Load existing shelters
            const loadShelters = async () => {
                try {
                    const response = await fetch('/api/shelters');
                    const shelters = await response.json();
                    
                    if (shelters.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4">No shelters registered yet.</td></tr>';
                        return;
                    }

                    tableBody.innerHTML = '';
                    const mapMarkers = [];

                    shelters.forEach(s => {
                        // Add to Map (Default Blue Marker)
                        const m = L.marker([s.latitude, s.longitude])
                            .addTo(map)
                            .bindPopup(`<strong>${s.name}</strong><br>Capacity: ${s.capacity || 'N/A'}`);
                        
                        mapMarkers.push([s.latitude, s.longitude]);

                        // Add to Table
                        const row = document.createElement('tr');
                        
                        const nameCell = document.createElement('td');
                        const strong = document.createElement('strong');
                        strong.textContent = s.name;
                        nameCell.appendChild(strong);
                        row.appendChild(nameCell);

                        const coordsCell = document.createElement('td');
                        coordsCell.textContent = `${s.latitude}, ${s.longitude}`;
                        row.appendChild(coordsCell);

                        const capacityCell = document.createElement('td');
                        capacityCell.textContent = s.capacity || 'Unknown';
                        row.appendChild(capacityCell);

                        const dateCell = document.createElement('td');
                        dateCell.textContent = new Date(s.created_at).toLocaleDateString();
                        row.appendChild(dateCell);
                        
                        tableBody.appendChild(row);
                    });

                    // Auto-zoom map to show all markers if they exist
                    if (mapMarkers.length > 0) {
                        map.fitBounds(L.latLngBounds(mapMarkers).pad(0.1));
                    }
                } catch (e) {
                    console.error("Error loading shelters:", e);
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-danger">Failed to load registry.</td></tr>';
                }
            };

            // 2. Handle map clicks for NEW shelter
            map.on('click', (e) => {
                const { lat, lng } = e.latlng;
                
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);

                selectionMarker.setLatLng(e.latlng).addTo(map);
                selectionMarker.bindPopup("<strong>Selected Location</strong>").openPopup();
            });

            loadShelters();
        });
    </script>
</body>
</html>
