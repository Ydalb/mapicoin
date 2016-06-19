var map;
var markers = [];
var GeoMarker;
var is_geolocated=false;
var directionsDisplay;
var directionsService = new google.maps.DirectionsService();



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
    console.log(GeoMarker);
    google.maps.event.addListenerOnce(GeoMarker, 'position_changed',     function() {
        is_geolocated = true;
    });

    // For distance
    directionsDisplay.setMap(map);

    return map;
}



/**
 * Bind ad pop-up to marker
 */
function bind_info_window(marker, map, infowindow, description) {
    marker.addListener('click', function() {
        infowindow.setContent(description);
        infowindow.open(map, this);
        this.setLabel('');
        this.setIcon('//mt.google.com/vt/icon?color=ff004C13&name=icons/spotlight/spotlight-waypoint-blue.png');
    });
}


/**
 * Add markers to the map
 */
function add_ads_markers(map, ads) {
    //create empty LatLngBounds object
    var bounds     = new google.maps.LatLngBounds();
    var infowindow = new google.maps.InfoWindow({
        content: ""
    });

    for (var index in ads) {

        var ad       = ads[index];
        var myLatlng = new google.maps.LatLng(ad.latlng.lat, ad.latlng.lng);

        var marker   = new google.maps.Marker({
            map:      map,
            position: myLatlng,
            title:    ad.title,
            label: {
                text : ad.count.toString(),
            }
        });

        // On click event (calculate distance)
        marker.addListener('click', function() {
            calc_distance_to_marker(this);
        });

        markers.push(marker);

        //http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=10|FE7569

        bind_info_window(marker, map, infowindow, ad.text);
        //extend the bounds to include each marker's position
        bounds.extend(marker.position);

    }

    //now fit the map to the newly inclusive bounds
    map.fitBounds(bounds);
}


/**
 * Remove all markers from the map
 */
function remove_markers() {
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
    directionsService.route(request, function(result, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(result);
            var distance = result.routes[0].legs[0].distance.text;
            var time     = result.routes[0].legs[0].duration.text;
            $('.list_item .item_distance').html('Trajet : '+distance+' ('+time+')');
        }
    });
}
