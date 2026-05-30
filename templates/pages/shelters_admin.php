<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Manage Shelters</title>
    <link rel="stylesheet" href="/css/style.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        #map { height: 500px; width: 100%; border-radius: 8px; margin-bottom: 20px; }
        .shelter-form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-save { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .coords-info { background: #e9ecef; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Shelters</h1>
            <nav>
                <a href="/" class="btn">Dashboard</a>
            </nav>
        </header>

        <main>
            <section class="map-section">
                <h2>1. Select Location on Map</h2>
                <p>Click anywhere on the map to set the coordinates for a new shelter.</p>
                <div id="map"></div>
            </section>

            <section class="form-section">
                <h2>2. Shelter Details</h2>
                <?php if(isset($_GET['success'])): ?>
                    <p style="color: green; font-weight: bold;">✅ Shelter added successfully!</p>
                <?php endif; ?>

                <form action="/admin/shelters/add" method="POST" class="shelter-form">
                    <div class="coords-info">
                        <strong>Latitude:</strong> <span id="display-lat">None</span> | 
                        <strong>Longitude:</strong> <span id="display-lng">None</span>
                    </div>

                    <!-- Hidden fields to store coordinates -->
                    <input type="hidden" name="latitude" id="input-lat" required>
                    <input type="hidden" name="longitude" id="input-lng" required>

                    <div class="form-group">
                        <label for="name">Shelter Name:</label>
                        <input type="text" name="name" id="name" placeholder="e.g. Community Center A" required>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity (Optional):</label>
                        <input type="number" name="capacity" id="capacity" placeholder="e.g. 200">
                    </div>

                    <button type="submit" class="btn-save">Save Shelter</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize map centered at a default location (e.g., Iasi)
            const map = L.map('map').setView([47.173780, 27.574728], 20);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const marker = L.marker([0, 0]);
            const latDisplay = document.getElementById('display-lat');
            const lngDisplay = document.getElementById('display-lng');
            const latInput = document.getElementById('input-lat');
            const lngInput = document.getElementById('input-lng');

            // Handle map click
            map.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                // Update UI
                latDisplay.textContent = lat;
                lngDisplay.textContent = lng;
                
                // Set form values
                latInput.value = lat;
                lngInput.value = lng;

                // Move marker
                marker.setLatLng(e.latlng).addTo(map);
            });

            // Fetch and show existing shelters
            fetch('/api/shelters')
                .then(res => res.json())
                .then(data => {
                    data.forEach(shelter => {
                        L.circle([shelter.latitude, shelter.longitude], {
                            color: 'green',
                            fillColor: '#28a745',
                            fillOpacity: 0.5,
                            radius: 500
                        }).addTo(map).bindPopup(`<b>${shelter.name}</b><br>Capacity: ${shelter.capacity || 'Unknown'}`);
                    });
                });
        });
    </script>
</body>
</html>
