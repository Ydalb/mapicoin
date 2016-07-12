var map;
var GeoMarker, GeoCircle, GeoCircleDistance = 0, currentActiveMarker;
var markers           = [];
var is_geolocated     = false;
var directionsDisplay;
var directionsService = new google.maps.DirectionsService();
var infowindow        = new google.maps.InfoWindow({
    content: ""
});
var iconDefault = {
   url: '//maps.google.com/mapfiles/ms/icons/red-dot.png'
};
var iconActive  = {
   url: '//maps.google.com/mapfiles/ms/icons/green-dot.png'
};
var iconGps = {
    'url':        '/img/gpsloc.png',
    'size':       new google.maps.Size(34, 34),
    'scaledSize': new google.maps.Size(17, 17),
    'origin':     new google.maps.Point(0, 0),
    'anchor':     new google.maps.Point(8, 8)
};


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
    // content.push('<p><img class="marker" src="'+iconDefault+'" /> : annonce non visitée</p>');
    // content.push('<p><img class="marker" src="//maps.google.com/mapfiles/ms/icons/green-dot.png" /> : annonces multiples non visitées</p>');
    // content.push('<p><img class="marker" src="'+iconGps+'" /> : votre position (peut être changée)</p>');
    // legend.innerHTML = content.join('');
    // legend.index     = 1;
    // map.controls[google.maps.ControlPosition.RIGHT_TOP].push(legend);

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
    // GeoMarker = new GeolocationMarker(map);
    // GeoMarker.setMarkerOptions({draggable: true});
    // google.maps.event.addListenerOnce(GeoMarker, 'position_changed', function() {
    //     is_geolocated = true;
    // });
    get_user_location();

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

        var marker   = new google.maps.Marker({
            id:       i,
            map:      map,
            position: new google.maps.LatLng(data.latlng.lat, data.latlng.lng),
            icon:     iconDefault
        });

        // On click event (calculate distance)
        marker.addListener('click', function() {
            set_icon_markers(iconDefault);
            if (is_geolocated) {
                var trajet = calc_distance_to_marker(this);
                var tmpMarkers = [GeoMarker, this];
                map_fit_bounds(tmpMarkers);
            }
            this.setIcon(iconActive);
            currentActiveMarker = this;
            panel_highlight(this.id);
        });

        marker.addListener('visible_changed', function() {
            panel_toggle_item(this.id, this.getVisible());
        })

        markers.push(marker);

    }

    return update_marker_from_circle();
}

/**
 * Bind une tooltip sur les markeurs
 */
function bind_info_window(marker, text) {
    infowindow.setContent(text);
    infowindow.open(map, marker);
}


/**
 * Recalcul le zoom de la map pour que tous les markeurs soient visibles
 */
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

/**
 * Défini une icône pour tous les markeurs
 */
function set_icon_markers(icon) {
    if (!icon) {
        icon = iconDefault;
    }
    for (var i = 0; i < markers.length; i++) {
        markers[i].setIcon(icon)
    }
}

/**
 * Re-calcul la distance vers le dernier marker actif
 */
function calc_distance_to_last_active_marker() {
    if (!currentActiveMarker) {
        return false;
    }
    return calc_distance_to_marker(currentActiveMarker);
}

/**
 * Calcul la distance de sa géoloc à un marker passé en paramètre
 */
function calc_distance_to_marker(marker) {
    if (!is_geolocated) {
        return false;
    }
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

/**
 * Mets à jour les markeurs suivant le cercle de distance
 */
function update_marker_from_circle() {
    if (!is_geolocated) {
        return false;
    }
    if (!GeoCircle) {
        return false;
    }
    for (var i = 0; i < markers.length; i++) {
        var d = google.maps.geometry.spherical.computeDistanceBetween(
            markers[i].getPosition(),
            GeoMarker.getPosition()
        );
        if (GeoCircle.getRadius() > 0 && d > GeoCircle.getRadius()) {
            markers[i].setVisible(false);
        } else {
            markers[i].setVisible(true);
        }
    }
    // With lazyload, we need to force it (little bug)
    $(".lazyload").trigger('appear');
    return panel_update_count();
}
/**
 * Trace un cercle autour de la localisation GPS
 */
function draw_circle_around_user_location() {
    if (!is_geolocated) {
        return false;
    }
    if (!GeoCircle) {
        GeoCircle = new google.maps.Circle({
            // center: GeoMarker.getPosition(),
            // radius: kilometer * 1000,
            fillColor: "#0000FF",
            fillOpacity: 0.15,
            map: map,
            strokeColor: "#FFFFFF",
            strokeOpacity: 0.1,
            strokeWeight: 2
        });
    }
    GeoCircle.setRadius(GeoCircleDistance);
    GeoCircle.setCenter(GeoMarker.getPosition());
    if (GeoCircleDistance == 0) {
        GeoCircle.setVisible(false);
    } else {
        GeoCircle.setVisible(true);
    }
    return update_marker_from_circle();
}
/**
 * Défini la distance du cercle de recherche
 */
function set_user_distance(kilometer) {
    if (kilometer > 0) {
        GeoCircleDistance = kilometer * 1000;
    } else {
        GeoCircleDistance = 0;
    }
    return draw_circle_around_user_location();
}
/**
 * Défini la position de l'utilisateur via un markeur
 */
function set_user_location(position) {
    is_geolocated  = true;
    GeoMarker      = null;
    var markerOpts = {
        'map':       map,
        'cursor':    'pointer',
        'draggable': true,
        'flat':      true,
        'icon':      iconGps,
        'position':  new google.maps.LatLng(
            position.coords.latitude,
            position.coords.longitude
        ),
        'title':  "Votre position actuelle. Déplacez-moi si besoin !",
        'zIndex': 2
    };
    GeoMarker = new google.maps.Marker(markerOpts);
    GeoMarker.addListener('drag',function(event) {
        draw_circle_around_user_location();
    });
    GeoMarker.addListener('dragend',function(event) {
        calc_distance_to_last_active_marker();
    });
    // Draw blue circle
    draw_circle_around_user_location();
}

/**
 * Récupération de la position via le navigateur
 */
function get_user_location() {
    var lat = null,
        lng = null;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(set_user_location);
    } else {
      // Pas de support, proposer une alternative ?
      alert("Votre navigateur ne supporte pas la géolocalisation. À la place, veuillez utiliser le formulaire prévu à cet effet.");
    }
}
