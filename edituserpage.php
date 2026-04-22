<?php
session_start();
include 'config.php';
include 'includes/header.php';
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user data
$sql = "SELECT c.*, u.email FROM customer c 
        INNER JOIN user u ON c.user_id = u.user_id 
        WHERE u.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $user = []; // fallback to avoid offset errors
    $_SESSION['error'] = "User not found in the customer table.";
}
$email = $_SESSION['email'];
// Get user_id first
$userSql = "SELECT user_id FROM user WHERE email = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    die("User not found.");
}

$user_id = $userData['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $province = "Batangas"; // Force Batangas province
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];

  

    $update = $conn->prepare("UPDATE customer SET 
    name = ?, phone = ?, province = ?, municipalities = ?, barangay = ?, street = ?, latitude = ?, longitude = ? 
    WHERE user_id = ?");
$update->bind_param("ssssssssd", 
    $name, 
    $phone, 
    $province, 
    $municipality, 
    $barangay, 
    $street, 
    $lat, 
    $lng, 
    $user_id
);

    if ($update->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: edituserpage.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .map-container {
            position: relative;
        }
        .locate-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            padding: 8px 12px;
            background: white;
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            cursor: pointer;
        }
        .profile-card {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .address-select {
            transition: all 0.3s ease;
        }
        .error-message {
    color: #dc3545;
    font-size: 0.9rem;
    margin-top: 5px;
}
    </style>
</head>
<body>
<?php include 'navs/usernav.php'; ?>

<div class="container">
    <div class="profile-card card">
        <h2 class="mb-4 text-center">Edit Profile</h2>
        <?php if (isset($_SESSION['address_error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['address_error'] ?>
        <?php unset($_SESSION['address_error']); ?>
    </div>
<?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" 
                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" name="phone" 
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <!-- Address Section -->
            <div class="mb-3">
                <label class="form-label">Province</label>
                <select class="form-select" id="province" name="provice" disabled>
                    <option value="Batangas" selected>Batangas</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Municipality/City</label>
                <select class="form-select" id="municipality" name="municipality" required>
                    <option value="" disabled selected>Loading municipalities...</option>
                </select>
            </div>
            <input type="hidden" name="municipality" id="municipality-name" value="">
            <div class="mb-3">
                <label class="form-label">Barangay</label>
                <select class="form-select" id="barangay" name="barangay" required>
                    <option value="" disabled selected>Select municipality first</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Street/Purok</label>
                <input type="text" class="form-control" name="street" 
                       value="<?php echo htmlspecialchars(explode(',', $user['street'])[0] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Location Pinning</label>
                <div class="map-container">
                    <button type="button" class="locate-btn" onclick="locateUser()">
                        📍 Use Current Location
                    </button>
                    <div id="map"></div>
                </div>
                <input type="hidden" name="latitude" id="latitude" 
                    value="<?php echo $user['latitude'] ?? ''; ?>">
                <input type="hidden" name="longitude" id="longitude" 
                    value="<?php echo $user['longitude'] ?? ''; ?>">
            </div>


            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="userpage.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Map and Marker variables
    let map;
    let marker;
    
    // Initialize Map
    function initMap() {
        const defaultLat = <?php echo isset($user['latitude']) ? $user['latitude'] : '13.7565'; ?>;
        const defaultLng = <?php echo isset($user['longitude']) ? $user['longitude'] : '121.0583'; ?>;
        
        map = L.map('map').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Initialize marker if coordinates exist
        if(defaultLat && defaultLng) {
            marker = L.marker([defaultLat, defaultLng], { 
                draggable: true 
            }).addTo(map);
            marker.on('dragend', handleMapUpdate);
        }

        // Map click handler
        map.on('click', function(e) {
            if(marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, { 
                    draggable: true 
                }).addTo(map);
                marker.on('dragend', handleMapUpdate);
            }
            handleMapUpdate();
            debouncedReverseGeocode(e.latlng.lat, e.latlng.lng);
        });
    }

    // Handle map updates
    function handleMapUpdate() {
        const coords = marker.getLatLng();
        document.getElementById('latitude').value = coords.lat.toFixed(6);
        document.getElementById('longitude').value = coords.lng.toFixed(6);
    }

    // Geolocation
    function locateUser() {
        if (!navigator.geolocation) return alert("Geolocation not supported");
        
        navigator.geolocation.getCurrentPosition(
            position => {
                const pos = [position.coords.latitude, position.coords.longitude];
                map.setView(pos, 15);
                if(marker) {
                    marker.setLatLng(pos);
                } else {
                    marker = L.marker(pos, { 
                        draggable: true 
                    }).addTo(map);
                    marker.on('dragend', handleMapUpdate);
                }
                debouncedReverseGeocode(...pos);
            },
            error => alert('Error: ' + error.message)
        );
    }

    // Reverse Geocoding
    async function reverseGeocode(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`,
                { headers: { 'User-Agent': 'YourAppName' } }
            );
            const data = await response.json();
            updateAddressFields(data.address);
        } catch (error) {
            console.error('Geocoding error:', error);
        }
    }

    // Update form fields with address data
    async function updateAddressFields(address) {
        // Update street
        if(address.road) {
            document.querySelector('input[name="street"]').value = address.road;
        }

        // Process municipality/city
        const city = address.city || address.town || address.municipality || '';
        const normalizedCity = city.replace(/\s+City$/, '').trim();
        if(normalizedCity) {
            await selectMunicipality(normalizedCity);
        }

        // Process barangay
        const barangay = address.village || address.suburb || address.neighbourhood || '';
        if(barangay && document.getElementById('barangay').options.length > 0) {
            await selectBarangay(barangay);
        }
    }

    // Select municipality in dropdown
    async function selectMunicipality(cityName) {
        const municipalitySelect = document.getElementById('municipality');
        const option = [...municipalitySelect.options].find(opt => 
            opt.text.replace(/\s+City$/, '').trim() === cityName
        );
        
        if(option) {
            municipalitySelect.value = option.value;
            await loadBarangays(option.value);
        }
    }

    // Select barangay in dropdown
    async function selectBarangay(barangayName) {
        const barangaySelect = document.getElementById('barangay');
        const option = [...barangaySelect.options].find(opt => 
            opt.text.toLowerCase() === barangayName.toLowerCase()
        );
        if(option) barangaySelect.value = option.value;
    }

    // PSGC API Integration
    async function loadBarangays(municipalityCode) {
        try {
            const response = await fetch(`https://psgc.gitlab.io/api/municipalities/${municipalityCode}/barangays`);
            const barangays = await response.json();
            
            const select = document.getElementById('barangay');
            select.innerHTML = '<option value="" disabled selected>Select barangay</option>';
            
            barangays.forEach(barangay => {
                const option = new Option(barangay.name, barangay.name);
                select.add(option);
            });
            return true;
        } catch (error) {
            console.error('Error loading barangays:', error);
            return false;
        }
    }

    // Debounce geocoding requests
    const debouncedReverseGeocode = debounce(reverseGeocode, 1000);
    function debounce(func, timeout = 1000) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), timeout);
        };
    }

    // Initialization
    window.addEventListener('DOMContentLoaded', () => {
        initMap();
        initializeProvince();
        // Reverse geocode existing coordinates
        if(document.getElementById('latitude').value && document.getElementById('longitude').value) {
            debouncedReverseGeocode(
                parseFloat(document.getElementById('latitude').value),
                parseFloat(document.getElementById('longitude').value)
    )}
    });

    async function initializeProvince() {
        try {
            const response = await fetch('https://psgc.gitlab.io/api/provinces/041000000');
            const batangas = await response.json();
            loadMunicipalities(batangas.code);
        } catch (error) {
            console.error('Error loading province:', error);
        }
    }

    async function loadMunicipalities(provinceCode) {
        try {
            const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/municipalities`);
            const municipalities = await response.json();
            
            const select = document.getElementById('municipality');
            select.innerHTML = '<option value="" disabled selected>Loading municipalities...</option>';
            
            municipalities.forEach(municipality => {
                const option = new Option(municipality.name, municipality.code);
                select.add(option);
            });
            
            // Set initial value if exists
            const initialMunicipality = `<?= $user['municipalities'] ?? '' ?>`;
            if(initialMunicipality) {
                const option = [...select.options].find(opt => opt.text === initialMunicipality);
                if(option) {
                    select.value = option.value;
                    loadBarangays(option.value);
                }
            }
        } catch (error) {
            console.error('Error loading municipalities:', error);
        }
    }
</script>
</body>
</html>