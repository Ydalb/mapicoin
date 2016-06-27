$(document).ready(function() {

    // ===
    // Reset slider when modal pop-up
    // ===
    $('#modal-help').on('show.bs.modal', function (e) {
        $('#carousel-mapicoin-howto').carousel(0);
    });

    // ===
    // Init. google map
    // ===
    map   = initialize_map();

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
        $('#map').hide();
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
                // Remove previous markers
                remove_markers();

                // Update URL
                var tmp = url.replace(/\?/g, '%3F').replace(/&/g, '%26');
                window.history.pushState("", "", "/?u="+tmp);

                // Group ads by lat/lng
                var datas = regroup_ads(data.datas);
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
                        $('#map').show();
                        lock_search(false);
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
        $('#input-submit').button('loading');
        $("body").css("cursor", "progress");
        $('#loader').show();
    } else {
        $('#input-submit').button('reset');
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
            '<div class="list_item">' +
                '<span class="item_distance"></span>' +
                '<a href="'+ad.url+'" title="'+ad.title+'" target="_blank">' +
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
                '</a>'+
            '</div>';

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

