$(document).ready(function() {

    // ===
    // Focus main input on load
    // ===
    $('#input-url').focus();

    // ===
    // "Back to search" button
    // ===
    $('body').on('click', '#new-search', function(event) {
        event.preventDefault();
        // Update URL
        window.history.pushState("", "", "/");
        // Clean input
        $('#loader').hide();
        $('#input-url').val('');
        $('#header').show();
        $('#new-search').hide();
        $('#map').html('').hide();
    })

    // ===
    // Form submit
    // ===
    $('#form-search').on('submit', function(event) {
        event.preventDefault();
        var $form = $(this);
        var url = $('#input-url').val();
        if (!url) {
            alert("Veuillez renseigner une URL de recherche leboncoin.fr");
            $('#input-url').focus();
            return false;
        }

        lock_search(true);

        $.ajax({
            url:      '/get-ads.php',
            type:     'post',
            data:     $form.serialize(),
            dataType: 'json',
            timeout:  60000,
            success: function(data) {
                if (!data.status) {
                    lock_search(false);
                    alert(data.message);
                    return false;
                }
                if (!data.datas || data.datas.length == 0) {
                    lock_search(false);
                    alert("Aucune ad trouvée. Veuillez essayer un autre lien de recherche.");
                    return false;
                }
                // Update URL
                var tmp = url.replace(/\?/g, '%3F').replace(/&/g, '%26');
                window.history.pushState("", "", "/?u="+tmp);

                // Group ads by lat/lng
                var datas = regroup_ads(data.datas);
                var map   = initialize_map();
                // Create and add markers to the map
                add_ads_markers(map, datas);

                // ScrollTo the map
                $('html, body').animate({
                    scrollTop:$('#map').offset().top
                    },
                    400,
                    function () {
                        $('#header').hide();
                        $('#new-search').show();
                        lock_search(false);
                        // Localize client
                        var GeoMarker = new GeolocationMarker(map);
                    }
                );
            },
            error: function() {
                lock_search(false);
                alert("Une erreur est survenue. Veuillez ré-essayer.");
            }
        })

    });


    // ===
    // Detect GET parameter and submit if needed
    // ===
    var u = parse_query_strings('u');
    if (u) {
        var tmp = u.replace(/%3F/g, '?').replace(/%26/g, '&');
        $('#input-url').val(tmp);
        $('#form-search').submit();
    }

});


/**
 * (un)lock the search form
 */
function lock_search(lock) {
    if (lock) {
        $('#input-submit').val('Chargement des annonces...');
        $('#input-submit').attr('disabled', 'disabled');
        $("body").css("cursor", "progress");
        $('#loader').show();
    } else {
        $('#input-submit').val($('#input-submit').data('value'));
        $('#input-submit').removeAttr('disabled');
        $("body").css("cursor", "default");
        $('#loader').hide();
    }
}

/**
 * Used to browse all ads and group them by location (lat/lng) in
 * order to have 1 marker for multiple ads
 */
function regroup_ads(datas) {

    var result = [];

    for (var i in datas) {

        var ad      = datas[i];
        ad['count'] = 1;
        ad['text']  = '' +
            '<a href="'+ad.url+'" title="'+ad.title+'" target="_blank" class="list_item">' +
                '<div class="item_image">' +
                    '<span class="item_imagePic">' +
                        '<img src="'+ad.picture+'">' +
                    '</span>' +
                    '<span class="item_imageNumber">'+ad.picture_count+'</span>' +
                '</div>' +
                '<section class="item_infos">' +
                    '<h2 class="item_title">'+ad.title+'</h2>' +
                    '<p class="item_supp">'+ad.pro+'</p>' +
                    '<p class="item_supp">'+ad.location+'</p>' +
                    '<h3 class="item_price">'+ad.price+'</h3>' +
                    '<aside class="item_absolute">' +
                        '<p class="item_supp">'+ad.date+'</p>' +
                    '</aside>' +
                '</section>' +
            '</a>';

        // Test if current ad has the same lat/lng of another ad
        var found = false;
        for (var j in result) {
            var tmp = result[j];
            // ad matching another one
            if (tmp.latlng.lat == ad.latlng.lat && tmp.latlng.lng == ad.latlng.lng) {
                found = true;
                result[j].text  += ad.text;
                result[j].count += 1;
                break;
            }
        }
        // If found, we add the pop-up content next to the current one('s)
        if (!found) {
            result.push(ad);
        }
    }

    return result;
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

        //http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=10|FE7569

        bind_info_window(marker, map, infowindow, ad.text);
        //extend the bounds to include each marker's position
        bounds.extend(marker.position);

    }

    //now fit the map to the newly inclusive bounds
    map.fitBounds(bounds);
}

/**
 * Bind ad pop-up to marker
 */
function bind_info_window(marker, map, infowindow, description) {
    marker.addListener('click', function() {
        infowindow.setContent(description);
        infowindow.open(map, this);
        this.setLabel('');
        this.setIcon('http://mt.google.com/vt/icon?color=ff004C13&name=icons/spotlight/spotlight-waypoint-blue.png');
    });
}

/**
 * Parse query strings (foo=bar&foo1=bar1) and return given parameter value
 */
function parse_query_strings(val) {
    var result = null,
        tmp    = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === val) {
                tmp.shift();
                result = tmp.join('=');
            }
        }
    );
    return result;
}

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


    return map;
}

function set_client_position(position) {
    console.log(position.coords);
}
function get_client_position() {

}

