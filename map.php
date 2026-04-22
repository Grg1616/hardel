<?php
session_start();
include 'config.php';
include 'includes/header.php';

// Check authentication
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$driver = [];
$orders = [];

// Get driver details
$driver_query = "SELECT d.* FROM driver d 
                 JOIN user u ON d.user_id = u.user_id
                 WHERE u.user_id = ?";
$stmt = $conn->prepare($driver_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$driver_result = $stmt->get_result();
if ($driver_result->num_rows > 0) {
    $driver = $driver_result->fetch_assoc();
}

// Get assigned orders
$order_query = "SELECT o.order_id, c.name AS customer_name,
                CONCAT(c.street, ', ', c.barangay, ', ', c.municipalities, ', ', c.province) AS full_address,
                c.latitude, c.longitude, o.order_date
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.driver_id = ? AND o.order_status = 'shipped'";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $driver['driver_id']);
$stmt->execute();
$order_result = $stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);

// Initialize delivery points array
$delivery_points = [];
foreach ($orders as $order) {
    if (!empty($order['latitude']) && !empty($order['longitude'])) {
        $delivery_points[] = [
            'lat' => $order['latitude'],
            'lng' => $order['longitude'],
            'info' => $order['customer_name'] . '<br>' . $order['full_address']
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Route</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        #map { height: 100vh; border-radius: 10px; }
        .driver-pulse {
            background-color: rgba(1, 113, 117, 0.33);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            position: relative;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(162, 163, 163, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(0, 255, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
        }
        .leaflet-routing-container {
        background-color: #2a2a2a !important;
        color: #fff !important;
        margin-right: 20px;
        margin-bottom: 20px;
        width: 300px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .leaflet-routing-container h2 {
        color: #fff !important;
        font-size: 1.2rem;
        padding: 10px;
        margin: 0;
        border-bottom: 1px solid #444;
    }

    .leaflet-routing-alt {
        background-color: #333 !important;
        color: #fff !important;
    }

    .leaflet-bar {
        background-color: #333 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .leaflet-bar a {
        background-color: #333 !important;
        color: #fff !important;
        border-bottom: 1px solid #444 !important;
    }



    .leaflet-routing-instructions {
        background-color: #2a2a2a !important;
    }

    .leaflet-routing-instructions-table {
        color: #fff !important;
    }
    #map { height: 60vh; border-radius: 10px; }
    .driver-pulse {
        background-color: rgba(1, 113, 117, 0.33);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        position: relative;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(162, 163, 163, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(0, 255, 136, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
    }

    /* Routing panel styles */
    .leaflet-routing-container {
        background-color: #333 !important;
        color: #fff !important;
        width: 300px;
        border-radius: 8px;
        transition: transform 0.3s ease;
        margin-right: 20px;
        margin-bottom: 50px; /* Space for toggle button */
    }

    .leaflet-routing-container.collapsed {
        transform: translateX(110%);
    }
    .panel-toggle-btn {
        position: absolute;
        right: 30px;
        bottom: 20px;
        z-index: 1000;
        background:#fff;
        color: black;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(105, 105, 105, 0.2);
    }

    .panel-toggle-btn:hover {
        background: beige;
    }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-3"><i class="bi bi-geo-alt"></i> Delivery Route</h1>
<div class="row">
 <div class="col-md-8">
 <div id="map">
 </div>
    <div class="d-flex justify-content-between align-items-center">
        <button class="btn btn-outline-light text-dark" onclick="recenterMap()"><i class="material-icons" style="font-size: 36px;">my_location</i></button>
        <a href="driver.php" class="btn btn-secondary">Back</a>
    </div>
    
 </div>
 <div class="col-md-4">
        <div class="card">
        <div class="card-body">
    <div class="mt-4">
        <h5>Delivery Orders</h5>
        <div style="max-height: 400px; overflow-y: auto;">
            <ul class="list-group">

                    <?php foreach ($orders as $point): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="order-<?= $point['order_id'] ?>">
                            <div>
                                <strong><?= htmlspecialchars($point['customer_name']) ?></strong><br>
                                <small><?= htmlspecialchars($point['full_address']) ?></small>
                            </div>
                            <button class="btn btn-success btn-sm" onclick="markOrderComplete(<?= $point['order_id'] ?>)">Complete</button>
                        </li>
                    <?php endforeach; ?>
                    </ul>
        </div>
    </div>


            </div>

        </div>
    </div>
    </div>
    <div class="row">
        <div class="bg-dark text-white">
            <div class="mt-4 py-1">
                <h5>Driver Details</h5>
                <div class=" bg-dark text-white py-2">
                    <h5>Name: <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?> | Phone: <?= htmlspecialchars($driver['phone']) ?></h5>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
const deliveryPoints = <?= json_encode($delivery_points) ?>;
let map, driverMarker, watchId, driverLocationGlobal, routingControl, panelToggle;
let markers = {}; 


function initMap() {
    // Initialize map with gray theme
    map = L.map('map', {
        maxZoom: 22,
        minZoom: 10
    }).setView([14.6, 121.0], 18);

    // Add gray theme base layer
    L.tileLayer('https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key=nrlOQ0tjPJmzy07N9wAh', {
        attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">© MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">© OpenStreetMap contributors</a>',
        maxZoom: 22
    }).addTo(map);

    // Add delivery markers
    deliveryPoints.forEach(point => {
        L.marker([point.lat, point.lng])
            .bindPopup(`<b class="text-dark">${point.customer}</b><br>${point.address}`)
            .addTo(map);
    });

    // Setup geolocation
    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(position => {
            const pos = [position.coords.latitude, position.coords.longitude];
            driverLocationGlobal = pos;

            if (!driverMarker) {
                const icon = L.divIcon({ className: 'driver-pulse' });
                driverMarker = L.marker(pos, { icon }).addTo(map);
            } else {
                driverMarker.setLatLng(pos);
            }

            map.setView(pos, 20);
            updateRoute(pos);
        }, handleGeolocationError, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 5000
        });
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

function updateRoute(currentPos) {
    if (routingControl) {
        map.removeControl(routingControl);
    }

    const waypoints = [
        L.latLng(currentPos[0], currentPos[1]),
        ...deliveryPoints.map(p => L.latLng(p.lat, p.lng))
    ];

    routingControl = L.Routing.control({
        waypoints,
        routeWhileDragging: true,
        show: true,
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        collapsible: false,
        position: 'bottomright',
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1'
        }),
        formatter: new L.Routing.Formatter({
            language: 'en',
            units: 'metric'
        })
    }).addTo(map);

    // Add toggle button for routing panel
    if (!panelToggle) {
        panelToggle = document.createElement('button');
        panelToggle.className = 'panel-toggle-btn';
        panelToggle.textContent = '▲ Hide Route';
        panelToggle.onclick = () => toggleRoutingPanel();
        map.getContainer().appendChild(panelToggle);
    }
}

function toggleRoutingPanel() {
    const panel = document.querySelector('.leaflet-routing-container');
    if (panel) {
        panel.classList.toggle('collapsed');
        panelToggle.textContent = panel.classList.contains('collapsed') 
            ? '▼ Show Route' 
            : '▲ Hide Route';
    }
}

function handleGeolocationError(error) {
    console.error('Geolocation error:', error);
    alert('Error getting location. Showing delivery points only.');
    if (deliveryPoints.length > 0) {
        map.setView([deliveryPoints[0].lat, deliveryPoints[0].lng], 15);
    }
}

function recenterMap() {
    if (driverLocationGlobal) {
        map.setView(driverLocationGlobal, 20);  // Use max zoom level
    }
}

window.addEventListener('beforeunload', () => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
});

document.addEventListener('DOMContentLoaded', initMap);

function markOrderComplete(orderId) {
    if (!confirm("Are you sure you want to mark this order as complete?")) return;

    fetch('complete_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_id=' + encodeURIComponent(orderId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById('order-' + orderId);
            item.classList.add('list-group-item-success');
            item.querySelector('button').remove(); // Remove "Complete" button
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Completion error:', error);
        alert("An error occurred while marking the order as complete.");
    });
}


</script>
</body>
</html>
