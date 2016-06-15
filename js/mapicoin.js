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
        $('html, body').animate({
            scrollTop:0
            },
            400,
            function () {
                lock_search(false);
                $('#new-search').hide();
            }
        );
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
            success: function(data) {
                if (!data.status) {
                    alert(data.message);
                    lock_search(false);
                    return false;
                }
                if (!data.datas || data.datas.length == 0) {
                    alert("Aucune ad trouvée. Veuillez essayer un autre lien de recherche.");
                    lock_search(false);
                    return false;
                }
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
                        $('#new-search').show();
                    }
                );
            },
            error: function() {
                alert("Une erreur est survenue. Veuillez ré-essayer.");
                lock_search(false);
            }
        })

    });

});


/**
 * (un)lock the search form
 */
function lock_search(lock) {
    if (lock) {
        $('#input-submit').val('Chargement des annonces...');
        $('#input-submit').attr('disabled', 'disabled');
        $("body").css("cursor", "progress");
    } else {
        $('#input-submit').val($('#input-submit').data('value'));
        $('#input-submit').removeAttr('disabled');
        $("body").css("cursor", "default");
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
            }
        }
        // If found, we add the pop-up content next to the current one('s)
        if (found) {
            result[j].text  += ad.text;
            result[j].count += 1;
        } else {
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

        var ad = ads[index];

        var marker  = new google.maps.Marker({
            map:      map,
            position: ad.latlng,
            title:    ad.title,
            icon:     'http://maps.google.com/mapfiles/ms/icons/'+(ad.count > 1 ? 'green' : 'red')+'-dot.png'
        });

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
        this.setIcon('http://maps.google.com/mapfiles/ms/icons/purple-dot.png');
    });
}

/**
 * Init google map
 */
function initialize_map() {
  var myLatLng = {lat: 47.351, lng: 3.392};
  var element  = document.getElementById('map');
  element.style.display = 'block';
  var map = new google.maps.Map(element, {
    center: myLatLng,
    scrollwheel: true,
    zoom: 6
  });
  return map;
}
