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
				height: 98%;
				padding: 0;
				margin: 0;
			}
			.toggle.ios, .toggle-on.ios, .toggle-off.ios{
				border-radius:20px;
			}
			.toggle.ios .toggle-handle{
				border-radius: 20px;
			}
			.fast .toggle-group{
				transition: left 0.1s;
				-webkit-transition: left 0.1s;
			}
			.modal-dialog.modal-sm{
				margin-top: 60px;
				margin-right: 10px;
				width: 360px; /*needed to increase modal size because title of first layer was too long*/
			}
			.modal.fade .modal-dialog {
				-webkit-transform: scale(0.1);
				-moz-transform: scale(0.1);
				-ms-transform: scale(0.1);
				transform: scale(0.1);
				top: 30px;
				left: 100px;
				opacity: 0.7;
				-webkit-transition: all 0.3s;
				-moz-transition: all 0.3s;
				transition: all 0.3s;
			}
			.modal.fade.in .modal-dialog {
				-webkit-transform: scale(1);
				-moz-transform: scale(1);
				-ms-transform: scale(1);
				transform: scale(1);
				-webkit-transform: translate3d(-100px, -30px, 0);
				transform: translate3d(-100px, -30px, 0);
				opacity: 1;
			}
			.modal-backdrop, .modal-backdrop.fade.in{
				opacity:0;
			}
		</style>
	</head>
	<!-- Navigation Bar -->
	<nav class="navbar navbar-default" style="margin:0; border:0; padding-right:15px; background-color:#FFFFFF">
		<div class="navbar-header">
			<div class="navbar-brand">BEC Navigation</div>
		</div>
		<div>
			<ul class="nav navbar-nav">
				<form class="navbar-form" style="padding-left:0; padding-right:0">
					<input type="text" class="form-control" placeholder="Search By Address" id="pac-input" style="width: 400px">	
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
		var layerSource = [{
			tableName: ["Primary_Map"],
			vizjson: "https://eulamue.cartodb.com/api/v2/viz/e2427020-0e2d-11e5-8e21-0e018d66dc29/viz.json"
		},{
			tableName:["ac_parcel_2015"],
			vizjson: "https://oakland.cartodb.com/u/oakland-admin/api/v2/viz/206c1a90-34b2-11e5-a5c5-42010a14cb63/viz.json"
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
		L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			maxZoom:22,
			maxNativeZoom:18 //zoom level to scale additional zoom layers
			}).addTo(map_object);
        
		var sublayers = [];
		cartodb.createLayer(map_object, layerSource[0].vizjson)
			.addTo(map_object)
			.done(function(layer){
				var sublayer = layer.getSubLayer(0);
				sublayers.push(sublayer);
			})
			.error(function(err){
				console.log("error: " + err);
			});
		cartodb.createLayer(map_object, layerSource[1].vizjson)
			.addTo(map_object)
			.done(function(layer){
				var sublayer = layer.getSubLayer(0);
				sublayers.push(sublayer);
				sublayer.hide();
			})
			.error(function(err){
				console.log("error: " + err);
			});
		initSearchBox();
		
		//initialize buttons and code to handle cases
		$('#Find').click(function(){
			var newQuery = "SELECT * FROM " + layerSource[0].tableName[0] + " WHERE apn = " + "'" + document.getElementById('textBox').value + "'";
			createQuery(newQuery, sublayers[0]);
		});
		
		$('#Clear').click(function(){
			var newQuery = "SELECT * FROM " + layerSource[0].tableName[0];
			createQuery(newQuery, sublayers[0]);
		});
		
		$('#layerModal').click(function(){
			$('#myModal').modal();
		});
		
		//class for toggles, 
		$('input[name="layerselect"]').change(function(){
			if($(this).is(":checked")){
				sublayers[this.value].show();
			}else{
				sublayers[this.value].hide();
			}
		});
		
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
		
		<!--Query Start-->	
		//re-written, this is all you need
		//still needs to be in main/onload unless you pass in the mapobject and the username or the username could just be a static
		//requires full query statement "Select * From tableName WHERE column = 'data'" and the layer object
		function createQuery(query, myLayer){
			var sql = new cartodb.SQL({user});
			myLayer.setSQL(query);
			sql.getBounds(query).done(function(bounds){
				map_object.fitBounds(bounds, {maxZoom:17});
			})
			return;
		}
		<!--Query End-->
	}
		// http://www.w3schools.com/bootstrap/default.asp
		// https://developers.google.com/maps/documentation/javascript/examples/places-searchbox
		// http://academy.cartodb.com/courses/03-cartodbjs-ground-up/lesson-1.html
    </script>
  </body>
</html>