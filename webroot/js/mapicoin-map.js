var map;
var markers = [];
var GeoMarker;



/**
 * Init google map
 */
function initialize_map() {
    var myLatLng          = {lat: 47.351, lng: 3.392};
    var element           = document.getElementById('map');
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
