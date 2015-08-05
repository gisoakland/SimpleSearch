<!DOCTYPE html>
<html>
	<head>
		<title>City Of Oakland Interactive Map</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<link rel="shortcut icon" href="http://cartodb.com/assets/favicon.ico" />
		
		<!-- CSS Styling -->
		<style type="text/css">
			html, body, #map {
				height: 97.8%;
				padding: 0;
				margin: 0;
			}
		</style>
	</head>
	<!-- Navigation Bar -->
	<nav class="navbar navbar-default" style="margin:0; border:0; padding-right:0px; background-color:#FFFFFF">
		<div class="navbar-header">
			<div class="navbar-brand">BEC Navigation</div>
		</div>
		<div>
			<ul class="nav navbar-nav">
				<form class="navbar-form" style="padding-left:0; padding-right:0">
					<input type="text" class="form-control" placeholder="Search By Address" id="pac-input" style="width:400px">	
				</form>
			</ul>
		</div>
	</nav>
    <div id="map"></div>
	
	<!--include scripts-->
	<!--Jquery needs to be before bootstrap-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<link rel="stylesheet" href="http://libs.cartocdn.com/cartodb.js/v3/3.14/themes/css/cartodb.css" />
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
	<link rel="stylesheet" href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" />
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=false&libraries=places"></script>
    <script src="http://libs.cartocdn.com/cartodb.js/v3/3.14/cartodb.js"></script>
    
    <!-- Drop your code between the script tags below! -->
    <script>
	window.onload = function() {
		var user = 'oakland-admin';
		//tableName needs to be an array containing the names of the tables within the vizjson
		//it's used for the querying functions & modal that are not in this file
		var layerSource = [{
			tableName: ["sewergrid"],
			vizjson: "https://eulamue.cartodb.com/api/v2/viz/e2427020-0e2d-11e5-8e21-0e018d66dc29/viz.json"
		}]
		// Initialize map center and zoom level
		var options = {
					center: [37.8,-122.21], // Oakland
					zoom: 12,
					maxZoom: 21
		}
		
		// Instantiate map on specified DOM element
		var map_object = new L.Map(map, options);
		
		// Add a basemap to the map object just created
		// maxNativeZoom refers to the maximum zoom the basemap provides, setting maxZoom will tell the map to stop requesting new tiles and to use a scaled version of the last requested tiledata
		L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			maxZoom:22,
			maxNativeZoom:18 //zoom level to scale additional zoom layers
			}).addTo(map_object);
        
		var sublayers = [];
		//don't try to use a loop to grab from different vizjsons
		//copy the createLayer code for each vizJson
		//create a Loop within the .done() function to automate the creation of layers within the vizJson
		//if you don't want a layer coming with the vizjson sublayer.hide() it instead of pushing it
		//hide layers here if you don't want it visible on load
		cartodb.createLayer(map_object, layerSource[0].vizjson)
			.addTo(map_object)
			.done(function(layer){
				var sublayer = layer.getSubLayer(0);
				sublayers.push(sublayer);
			})
			.error(function(err){
				console.log("error: " + err);
			});
		initSearchBox();
		
		<!-- Functions -->
		//not fully optimized
		<!--Geocoder Start-->
		function initSearchBox(){
			var bounds = map_object.getBounds();
			var latlngSW = new google.maps.LatLng(bounds._southWest.lat, bounds._southWest.lng);
			var latlngNE = new google.maps.LatLng(bounds._northEast.lat, bounds._northEast.lng);
			var defaultBound = new google.maps.LatLngBounds();
			defaultBound.extend(latlngSW);
			defaultBound.extend(latlngNE);
			
			var searchBox = new google.maps.places.SearchBox(document.getElementById('pac-input'), {bounds: defaultBound});
			var marker = null;
			google.maps.event.addListener(searchBox, 'places_changed', function(){
				var place = searchBox.getPlaces()[0];
				if(!place.geometry){
					return;
				}
				if(marker != null){
					map_object.removeLayer(marker);
				}
				map_object.setView(new L.LatLng(place.geometry.location.lat(), place.geometry.location.lng()), 17);
				marker = new L.marker([place.geometry.location.lat(), place.geometry.location.lng()],{clickable:false});
				map_object.addLayer(marker);
			});
		}
		<!--Geocoder End-->
	}
    </script>
  </body>
</html>
