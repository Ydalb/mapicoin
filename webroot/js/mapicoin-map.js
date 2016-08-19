var map, geocoder, GeoMarker, GeoCircle, currentActiveMarker;
var filterDistance    = 0
var filterTime        = 0;
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
    // console.log('function initialize_map() {');
    // Center of France
    // var myLatLng      = {lat: 47.351, lng: 3.392};
    var element       = document.getElementById('map');
    // For direction calculation
    opts = {
        preserveViewport:true,
        suppressMarkers:true
    }
    directionsDisplay = new google.maps.DirectionsRenderer(opts);
    $('#map').show();
    var map = new google.maps.Map(element, {
        center:      centerMap,
        scrollwheel: true,
        zoom:        6,
        mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.ROADMAP]
        },
        mapTypeControl: false,
        streetViewControl: false
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
                if (this.getZoom() > 13/* && this.initialZoom == true*/) {
                    // Change max/min zoom here
                    this.setZoom(13);
                    this.initialZoom = false;
                }
            // google.maps.event.removeListener(zoomChangeBoundsListener);
        });
    });
    map.initialZoom = true;

    //map loaded fully ?
    google.maps.event.addListenerOnce(map, 'idle', function(){
        // Retrieve user address from cookie ?
        retrieve_user_address_from_cookies();
    });

    // In order to localize client
    geocoder = new google.maps.Geocoder();

    // For distance
    directionsDisplay.setMap(map);

    return map;
}


/**
 * Add markers to the map
 */
function add_ads_markers(map, datas) {
    // console.log('function add_ads_markers(map, datas) {');

    for (var i in datas) {

        var data      = datas[i];
        var ads       = data.ads;

        var marker   = new google.maps.Marker({
            id:        i,
            map:       map,
            position:  new google.maps.LatLng(data.latlng.lat, data.latlng.lng),
            icon:      iconDefault,
            timestamp: data.timestamp
        });

        // On click event (calculate distance)
        marker.addListener('click', function() {
            set_icon_markers(iconDefault);
            if (is_geolocated) {
                var trajet     = calc_distance_to_marker(this);
            }
            this.setIcon(iconActive);
            currentActiveMarker = this;
            panel_highlight(this.id);
            // Open sidebar if mobile
            if (is_mobile()) {
                $('body').removeClass('toggle');
            }
        });

        marker.addListener('visible_changed', function() {
            panel_toggle_item(this.id, this.getVisible());
        })

        markers.push(marker);

    }

    return update_marker_from_filters();
}

/**
 * Bind une tooltip sur les markeurs
 */
function bind_info_window(marker, text) {
    // console.log('function bind_info_window(marker, text) {');
    infowindow.setContent(text);
    infowindow.open(map, marker);
    return map_fit_bounds([GeoMarker, marker]);
}


/**
 * Recalcul le zoom de la map pour que tous les markeurs soient visibles
 */
function map_fit_bounds(m) {
    // console.log('function map_fit_bounds(m) {');
    // Si on ne précise pas de marqueurs, on prends l'ensemble des marqueurs visibles
    if (!m) {
        var m = [];
        for (var i = 0; i < markers.length; i++) {
            if (markers[i].getVisible()) {
                m.push(markers[i]);
            }
        }
    }
    if (m.length == 0) {
        return false;
    }
    var bounds = new google.maps.LatLngBounds();
    for (var i in m) {
        bounds.extend(m[i].position);
    }

    map.setCenter(bounds.getCenter());
    map.fitBounds(bounds);
    map.setZoom(map.getZoom()-1);

    if (!$('body').hasClass('toggle')) {
        return offsetCenter(map.getCenter(), 200, 0);
    }
}


/**
 * Remove all markers from the map
 */
function remove_markers() {
    // console.log('function remove_markers() {');
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
    // console.log('function set_icon_markers(icon) {');
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
    // console.log('function calc_distance_to_last_active_marker() {');
    if (!currentActiveMarker) {
        return false;
    }
    return calc_distance_to_marker(currentActiveMarker);
}

/**
 * Calcul la distance de sa géoloc à un marker passé en paramètre
 */
function calc_distance_to_marker(marker) {
    // console.log('function calc_distance_to_marker(marker) {');
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
function update_marker_from_filters() {
    // console.log('function update_marker_from_filters() {');
    var currentTimestamp = Math.floor(Date.now() / 1000);
    var tooFar = false;
    var tooOld = true;
    for (var i = 0; i < markers.length; i++) {
        // Distance : 1 marker = plusieurs annonces avec même distance
        if (is_geolocated && GeoCircle && GeoCircle.getRadius() > 0) {
            var d = google.maps.geometry.spherical.computeDistanceBetween(
                markers[i].getPosition(),
                GeoMarker.getPosition()
            );
            // true si marker en dehors du cercle
            var tooFar = (d > GeoCircle.getRadius());
        }
        // Time : /!\ 1 marker = plusieurs annonces avec age différent /!\
        // On cherche donc à ce qu'une annonce au moins respecte la condition, pour afficher le marker
        if (filterTime > 0) {
            var id    = markers[i].id;
            // On parcours chaque annonce pour voir du marker pour voir si 1 match la condition 'day'
            tooOld    = true;
            var $pwet = $('#sidebar .pwet[data-index="'+id+'"] > .media').each(function( i ) {
                var timestamp   = $(this).data('timestamp');
                // true si annonce assez récente
                if (timestamp > (currentTimestamp - filterTime)) {
                    $(this).show();
                    tooOld = false;
                    return;
                } else {
                    // On cache l'annonce
                    $(this).hide();
                }
            });
        } else {
            tooOld = false;
        }

        if (tooFar || tooOld) {
            markers[i].setVisible(false);
        } else {
            markers[i].setVisible(true);
        }
    }
    // Pour le time, on cache les li où il n'y a plus d'annonces
    if (filterTime > 0) {
        $('#sidebar .pwet').each(function(i) {
            if ($(this).find('.media:visible').length == 0) {
                $(this).hide();
            }
        })
    }
    // With lazyload, we need to force it (little bug)
    $(".lazyload").trigger('appear');
    return panel_update_count();
}
/**
 * Trace un cercle autour de la localisation GPS
 */
function draw_circle_around_user_location() {
    // console.log('function draw_circle_around_user_location() {');
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
    GeoCircle.setRadius(filterDistance);
    GeoCircle.setCenter(GeoMarker.getPosition());
    if (filterDistance == 0) {
        GeoCircle.setVisible(false);
    } else {
        GeoCircle.setVisible(true);
    }
    return true;
}
/**
 * Défini la distance du cercle de recherche
 */
function set_user_distance(kilometer) {
    // console.log('function set_user_distance(kilometer) {');
    if (kilometer > 0) {
        filterDistance = kilometer * 1000;
    } else {
        filterDistance = 0;
    }
    return draw_circle_around_user_location();
}
/**
 * Défini le temps de recherche
 */
function set_user_day(nb_day) {
    // console.log('function set_user_day(nb_day) {');
    if (nb_day > 0) {
        filterTime = nb_day * 86400;
    } else {
        filterTime = 0;
    }
    return true;
}
/**
 * Défini la position de l'utilisateur via un markeur
 */
function set_user_location(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    // Remember position
    set_cookie('user_lat', lat, 30);
    set_cookie('user_lng', lng, 30);
    create_user_marker(lat, lng);
    get_address_from_latlng(lat, lng);

    custom_alert(
        "Position déterminée ! :-)",
        "Nous vous avons réussi à détecter votre position.<br />"+
            "<br />" +
            "Vous pouvez désormais utiliser les fonctionnalités liées à la géolocalisation sur <a>Mapicoin</a>",
        "success",
        {html: true, confirmButtonText: "C'est parti !", timer: 4000}
    );
}

/**
 * Fonction appelée si l'utilisateur refuse la localisation
 */
function user_denied_location() {
    // Pas de support, proposer une alternative ?
    custom_alert(
        "Position non déterminée :-(",
        "Votre navigateur ne semble pas supporter la géolocalisation automatique " +
            "ou vous avez refusé que l'on vous géolocalise.",
        "error",
        {html: true}
    );
    create_user_marker(centerMap.lat, centerMap.lng);
    get_address_from_latlng(centerMap.lat, centerMap.lng);
}

function create_user_marker(lat, lng) {
    // On a déjà été géoloc au moins une fois avant, on update simplement que la position
    var pos = new google.maps.LatLng(lat, lng);
    if (GeoMarker) {
        GeoMarker.setPosition(pos);
    } else {
        // enable localization filter
        $('.filter-item.filter-distance')
            .removeClass('disabled')
            .removeAttr('title')
            .find('select')
                .removeAttr('disabled');

        is_geolocated  = true;
        var markerOpts = {
            'map':       map,
            'cursor':    'pointer',
            'draggable': true,
            'flat':      true,
            'icon':      iconGps,
            'position':  pos,
            'title':  "Votre position actuelle. Déplacez-moi si besoin !",
            'zIndex': 2
        };
        GeoMarker = new google.maps.Marker(markerOpts);
        GeoMarker.addListener('drag',function(event) {
            draw_circle_around_user_location();
            // on-the-fly update is too slow !
            // update_marker_from_filters();
        });
        GeoMarker.addListener('dragend',function(event) {
            calc_distance_to_last_active_marker();
            update_marker_from_filters();
            // Remember position
            set_cookie('user_lat', GeoMarker.getPosition().lat(), 30);
            set_cookie('user_lng', GeoMarker.getPosition().lng(), 30);
            get_address_from_latlng(
                GeoMarker.getPosition().lat(),
                GeoMarker.getPosition().lng()
            );
        });
    }
    // Draw blue circle
    draw_circle_around_user_location();
    // Update markers
    update_marker_from_filters();
    // Fit bounds
    map_fit_bounds();
}


/**
 * Récupération de la position via le navigateur
 */
function get_user_location() {
    loader_user_location(true);
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(set_user_location, user_denied_location);
    } else {
        user_denied_location();
    }
}

function loader_user_location(status) {
    if (status) {
        $('#geolocalize-info').html($('#geolocalize-info').data('loader'));
        if (is_mobile()) {
            $('#geolocalize-me').html($('#geolocalize-me').data('loader'));
        }
    } else {
        if (is_mobile()) {
            $('#geolocalize-me').html($('#geolocalize-me').data('default'));
        }
    }
}

function offsetCenter(latlng, offsetx, offsety) {

    // latlng is the apparent centre-point
    // offsetx is the distance you want that point to move to the right, in pixels
    // offsety is the distance you want that point to move upwards, in pixels
    // offset can be negative
    // offsetx and offsety are both optional

    var scale = Math.pow(2, map.getZoom());

    var worldCoordinateCenter = map.getProjection().fromLatLngToPoint(latlng);
    var pixelOffset = new google.maps.Point((offsetx/scale) || 0,(offsety/scale) ||0)

    var worldCoordinateNewCenter = new google.maps.Point(
        worldCoordinateCenter.x - pixelOffset.x,
        worldCoordinateCenter.y + pixelOffset.y
    );

    var newCenter = map.getProjection().fromPointToLatLng(worldCoordinateNewCenter);

    map.setCenter(newCenter);

}

/**
 * Geocode des coordonnées en une adresse à partir de l'API Google
 */
function get_address_from_latlng(lat, lng) {
    // console.log(coords);
    var latlng = new google.maps.LatLng(lat, lng);
    $element = $('#geolocalize-info');
    loader_user_location(true);
    // AJAX call
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        loader_user_location(false);
        if (status != google.maps.GeocoderStatus.OK) {
            $element.html($element.data('default'));
            return false;
        }
        if (!results[1]) {
            $element.html($element.data('default'));
            return false;
        }
        // Remember position
        set_cookie('user_address', results[1].formatted_address, 30);
        set_user_address(results[1].formatted_address);
    });
}

/**
 * Affiche l'adresse de l'utilisateur
 * address : Paris, France
 */
function set_user_address(address) {
    var text = address.split(',');
    $('#geolocalize-info').text(text[0]);
    $('#geolocalize-info').attr('title', address);
}

function retrieve_user_address_from_cookies() {
    if (!get_cookie('user_address') || !get_cookie('user_lat') || !get_cookie('user_lng')) {
        return false;
    }
    // Affichage de l'adresse
    set_user_address(get_cookie('user_address'));
    // Création / MAJ du marqueur
    create_user_marker(get_cookie('user_lat'), get_cookie('user_lng'));
}
