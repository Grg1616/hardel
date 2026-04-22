<!DOCTYPE html>
<html>
<head>
  <title>MapQuest Directions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.css" />
  <style>
    #map {
      height: 100vh;
      width: 100%;
      position: relative;
    }

    .formBlock {
      max-width: 300px;
      background-color: #FFF;
      border: 1px solid #ddd;
      position: absolute;
      top: 10px;
      left: 10px;
      padding: 10px;
      z-index: 999;
      box-shadow: 0 1px 5px rgba(0,0,0,0.65);
      border-radius: 5px;
    }

    .input {
      padding: 10px;
      width: 100%;
      border: 1px solid #ddd;
      font-size: 15px;
      border-radius: 3px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body style="margin:0; padding:0;">

  <div id="map"></div>
  <div class="formBlock">
    <form id="form">
      <input type="text" name="end" class="input" id="destination" placeholder="Enter destination" />
      <button type="submit" class="input">Get Directions</button>
    </form>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.js"></script>
  <script src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=S8d7L47mdyAG5nHG09dUnSPJjreUVPeC"></script>
  <script src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-routing.js?key=S8d7L47mdyAG5nHG09dUnSPJjreUVPeC"></script>

  <script>
    let map = L.map('map', {
      layers: MQ.mapLayer(),
      center: [14.07233, 120.63958],
      zoom: 12
    });

    let currentRouteLayer = null;
    let userLocation = null;

    navigator.geolocation.getCurrentPosition(position => {
      userLocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };

      map.setView([userLocation.lat, userLocation.lng], 13);

      L.marker([userLocation.lat, userLocation.lng]).addTo(map)
        .bindPopup("You are here").openPopup();
    }, error => {
      alert("Geolocation failed. Please allow location access.");
      console.error(error);
    });

    function runDirection(end) {
      if (!userLocation) {
        alert("Waiting for current location...");
        return;
      }

      const start = `${userLocation.lat},${userLocation.lng}`;
      const dir = MQ.routing.directions();
      dir.route({
        locations: [start, end],
        options: {
          unit: 'k'
        }
      });

      const CustomRouteLayer = MQ.Routing.RouteLayer.extend({
        createStartMarker: function (location) {
          return L.marker(location.latLng, {
            icon: L.icon({
              iconUrl: 'img/red.png',
              iconSize: [20, 29],
              iconAnchor: [10, 29]
            })
          });
        },
        createEndMarker: function (location) {
          return L.marker(location.latLng, {
            icon: L.icon({
              iconUrl: 'img/blue.png',
              iconSize: [20, 29],
              iconAnchor: [10, 29]
            })
          });
        }
      });

      if (currentRouteLayer) {
        map.removeLayer(currentRouteLayer);
      }

      currentRouteLayer = new CustomRouteLayer({
        directions: dir,
        fitBounds: true
      });

      map.addLayer(currentRouteLayer);
    }

    document.getElementById('form').addEventListener('submit', function (e) {
      e.preventDefault();
      const end = document.getElementById("destination").value;
      if (end.trim() === "") {
        alert("Please enter a destination.");
        return;
      }
      runDirection(end);
    });
  </script>
</body>
</html>
