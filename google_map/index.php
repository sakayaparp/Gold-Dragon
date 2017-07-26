<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Map</title>
    <style>
      #map {
        height: 400px;
        width: 100%;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #floating-panel {
        position: absolute;
        top: 90px;
        left: 25%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
    </style>
  </head>
  <body>
    <div style="background-color: #f3d914;height: 80px;">
      <div style="text-align: center;padding-top: 30px;font-family: monospace;font-size: 20px;">
        <b>&copy; EGG Digital<b>
      </div>
    </div>
    <div id="floating-panel">
    <b>Mode of Travel: </b>
    <select id="mode">
      <option value="DRIVING">Driving</option>
      <option value="WALKING">Walking</option>
    </select>
    </div>
    <div id="map">
      <center>Loading</center>
    </div>
    <script>
      var map;
      var markers = [];
      var directionsService;
      var directionsDisplay;
      var latitude = '<?=$_GET['lat']?>';
      var longtitude = '<?=$_GET['lng']?>';
      var latitudeShop = '<?=$_GET['latShop']?>';
      var longtitudeShop = '<?=$_GET['lngShop']?>';

      function initMap() 
      {
        if(latitude == '' || longtitude == '') {
          getLocation();
        } else {
          latitude = Number(latitude);
          longtitude = Number(longtitude);
          // defind
          directionsService = new google.maps.DirectionsService;
          directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
          var userLocation = {lat: latitude, lng: longtitude};
          if(latitudeShop && longtitudeShop) {
            latitudeShop = Number(latitudeShop);
            longtitudeShop = Number(longtitudeShop);
            var foodLocations = [
              {lat: latitudeShop, lng: longtitudeShop}
            ];
          } else {
            var foodLocations = [
              {lat: 13.758914, lng: 100.5669539},
              {lat: 13.765966, lng: 100.569483},
              {lat: 14.585269, lng: 101.370326}
            ];  
          }

          // create map
          map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: userLocation
          });
          directionsDisplay.setMap(map);

          // mark food location into map
          foodLocations.map(function(index, elem) {
            createMarker(index);
          });

          // find best route
          var currentLocation = new google.maps.LatLng(userLocation.lat, userLocation.lng);
          find_closest_marker(currentLocation);
          document.getElementById('mode').addEventListener('change', function() {
            find_closest_marker(currentLocation);
          });
        }
      }

      function createMarker(place, iconOrigin = '') 
      {
        var image = 'http://www.egunner.com/themes/egunner/img/system/blue_star.gif.pagespeed.ce.ntMzRCcXYg.gif';
        image = (iconOrigin) ? iconOrigin : image;
        var marker = new google.maps.Marker({
          position: place,
          map: map,
          icon: image
        });
        if(iconOrigin == '') {
          markers.push(marker);  
        }
      }

      function find_closest_marker(latLng) 
      {
        var closestMarker = -1;
        var closestDistance = Number.MAX_VALUE;
        for( i=0 ; i < markers.length ; i++ ) {
          var distance = google.maps.geometry.spherical.computeDistanceBetween(markers[i].getPosition(), latLng);
          if ( distance < closestDistance ) {
              closestMarker = i;
              closestDistance = distance;
          }
        }
        calculateAndDisplayRoute(directionsService, directionsDisplay, latLng, markers[closestMarker].position);
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay, origin, destination) 
      {
        var selectedMode = document.getElementById('mode').value;
        directionsService.route({
          origin: origin,
          destination: destination,
          travelMode: google.maps.TravelMode[selectedMode]
        }, function(response, status) {
          if (status === 'OK') {
            var iconOrigin = 'http://www.thesource.ca/medias/account.svg?context=bWFzdGVyfGltYWdlc3wxMDc0fGltYWdlL3N2Zyt4bWx8aW1hZ2VzL2gxZi9oZGIvODgzNzc2MTY5NTc3NC5iaW58N2JiYmMyNmJiMGFhMjdhMTUxYTRiZWI0MWZhNjI0NjgyNjMxYjM1OTQxOGRmMmVhODU1YzI0ODI1ZDVkOWJkYQ';
            directionsDisplay.setDirections(response);
            var leg = response.routes[0].legs[0];
            createMarker(leg.start_location, iconOrigin);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }

      function getLocation() 
      {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            latitude = position.coords.latitude;
            longtitude = position.coords.longitude;
            initMap();
          });
        } else {
          console.log('Geolocation is not supported by this browser.');
        }
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY&callback=initMap&libraries=places,geometry">
    </script>
  </body>
</html>

