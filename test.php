<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>MapTiler Routing Example</title>
  <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no" />
  <script src="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.js"></script>
  <link href="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.css" rel="stylesheet" />
  <style>
    body { margin: 0; padding: 0; }
    #map { position: absolute; top: 0; bottom: 0; width: 100%; }
  </style>
</head>
<body>
<div id="map"></div>
<script>
  const map = new maplibregl.Map({
    container: 'map',
    style: 'https://api.maptiler.com/maps/streets/style.json?key=nrlOQ0tjPJmzy07N9wAh',
    center: [120.9842, 14.5995], // Manila, for example
    zoom: 12
  });

  // Points A and B (example: Manila to Quezon City)
  const pointA = [120.9842, 14.5995];  // A: Manila
  const pointB = [121.0437, 14.6760];  // B: Quezon City

  map.on('load', async () => {
    // Add markers
    new maplibregl.Marker().setLngLat(pointA).addTo(map);
    new maplibregl.Marker().setLngLat(pointB).addTo(map);

    // Get route from OpenRouteService
    const orsApiKey = '5b3ce3597851110001cf6248a5d1da648d5a4b079a8a3070392621c6';
    const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${orsApiKey}`;
    
    const body = {
      coordinates: [pointA, pointB]
    };

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Authorization': orsApiKey,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });

    const json = await response.json();
    const route = json.features[0].geometry;

    // Add route to map
    map.addSource('route', {
      type: 'geojson',
      data: {
        type: 'Feature',
        geometry: route
      }
    });

    map.addLayer({
      id: 'route',
      type: 'line',
      source: 'route',
      layout: {
        'line-cap': 'round',
        'line-join': 'round'
      },
      paint: {
        'line-color': '#ff0000',
        'line-width': 4
      }
    });
  });
</script>
</body>
</html>
