var map;
var markers = [];
var GeoMarker;
var is_geolocated=false;
var directionsDisplay;
var directionsService = new google.maps.DirectionsService();
var infowindow = new google.maps.InfoWindow({
    content: ""
});

var iconDefault = '//maps.google.com/mapfiles/ms/icons/red-dot.png';
var iconHover   = '//maps.google.com/mapfiles/ms/icons/green-dot.png';


/**
 * Init google map
 */
function initialize_map() {
    // Center of France
    var myLatLng      = {lat: 47.351, lng: 3.392};
    var element       = document.getElementById('map');
    // For direction calculation
    opts = {
        preserveViewport:true,
        suppressMarkers:true
    }
    directionsDisplay = new google.maps.DirectionsRenderer(opts);
    $('#map').show();
    var map = new google.maps.Map(element, {
        center:      myLatLng,
        scrollwheel: true,
        zoom:        6
    });
    // Create the legend and display on the map
    // var legend  = document.createElement('div');
    // legend.id   = 'legend';
    // var content = [];
    // content.push('<h3>Légende</h3>');
    // content.push('<p><img class="marker" src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" /> : annonce non visitée</p>');
    // content.push('<p><img class="marker" src="https://maps.google.com/mapfiles/ms/icons/green-dot.png" /> : annonces multiples non visitées</p>');
    // content.push('<p><img class="marker" src="https://maps.google.com/mapfiles/ms/icons/purple-dot.png" /> : annonce visitée</p>');
    // legend.innerHTML = content.join('');
    // legend.index     = 1;
    // map.controls[google.maps.ControlPosition.LEFT_TOP].push(legend);

    // This is needed to set the zoom after fitbounds,
    google.maps.event.addListener(map, 'zoom_changed', function() {
        zoomChangeBoundsListener =
            google.maps.event.addListener(map, 'bounds_changed', function(event) {
                if (this.getZoom() > 11 && this.initialZoom == true) {
                    // Change max/min zoom here
                    this.setZoom(11);
                    this.initialZoom = false;
                }
            google.maps.event.removeListener(zoomChangeBoundsListener);
        });
    });
    map.initialZoom = true;

    // Localize client
    GeoMarker = new GeolocationMarker(map);
    google.maps.event.addListenerOnce(GeoMarker, 'position_changed', function() {
        is_geolocated = true;
    });

    // For distance
    directionsDisplay.setMap(map);

    return map;
}


/**
 * Add markers to the map
 */
function add_ads_markers(map, datas) {


    for (var i in datas) {

        var data      = datas[i];
        var ads       = data.ads;
        var myLatlng  = new google.maps.LatLng(data.latlng.lat, data.latlng.lng);

        var marker   = new google.maps.Marker({
            id: i,
            map:      map,
            position: myLatlng,
            icon: iconDefault
            // label: {
            //     text : ads.length.toString(),
            // }
        });

        // On click event (calculate distance)
        marker.addListener('click', function() {
            var trajet = calc_distance_to_marker(this);
            var tmpMarkers = [GeoMarker, this];
            map_fit_bounds(tmpMarkers);
            panel_highlight(this.id);
        });

        markers.push(marker);

    }
}


function bind_info_window(marker, text) {
    infowindow.setContent(text);
    infowindow.open(map, marker);
}


function map_fit_bounds(m) {
    if (!m) {
        var m = markers;
    }
    var bounds = new google.maps.LatLngBounds();
    for (var i in m) {
        bounds.extend(m[i].position);
    }
    map.fitBounds(bounds);
}


/**
 * Remove all markers from the map
 */
function remove_markers() {
    directionsDisplay.setMap(null);
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
}


function calc_distance_to_marker(marker) {

    var request = {
        origin:      GeoMarker.getPosition(),
        destination: marker.getPosition(),
        travelMode:  google.maps.TravelMode.DRIVING
    };
    directionsDisplay.setMap(map);
    directionsService.route(request, function(result, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(result);
            var distance = result.routes[0].legs[0].distance.text;
            var time     = result.routes[0].legs[0].duration.text;
            var trajet   = '<div class="trajet">'+
                '<p>Distance : <span class="distance">'+distance+'</span></p>'+
                '<p>Temps : <span class="temps">'+time+'</span></p>'+
            '</div>';
            bind_info_window(marker, trajet);
        }
    });
}
