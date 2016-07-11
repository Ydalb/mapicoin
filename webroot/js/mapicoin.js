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
    });


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

                // count
                var count = Object.keys(data.datas).length;

                // Group ads by lat/lng
                var datas = regroup_ads(data.datas);

                // Toggle panel
                panel_toggle(true);

                // Create and add markers to the map + bind
                add_ads_markers(map, datas);

                // Fit bounds
                map_fit_bounds();

                // Update panel + Bind
                panel_update(data.title, datas, count);

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
        $('#input-url').prop('readonly', true);
        $("body").css("cursor", "progress");
        $('#loader').show();
    } else {
        $('#input-submit').button('reset');
        $('#input-url').prop('readonly', false);
        $("body").css("cursor", "default");
        $('#loader').hide();
    }
}

function panel_toggle(toggle) {
    if (toggle) {
        $('#sidebar-wrapper').addClass('toggled');
        $('#map').addClass('toggled');
        $('#overlay').fadeOut();
    } else {
        $('#sidebar-wrapper').removeClass('toggled');
        $('#map').removeClass('toggled');
        $('#overlay').fadeIn();
    }
    google.maps.event.trigger(map, "resize");
}

function panel_update(title, datas, count) {
    // Title + Content + Edit
    $('.sidebar-title').text(title).attr('title', title);
    $('.sidebar-count').html('');
    $('.sidebar-edit-search').attr('href', $('#input-url').val());
    $('#sidebar').html('');

    // Update content
    for (var i in datas) {
        var ads = datas[i].ads;
        for (var j in ads) {
            $('#sidebar').append(ads[j].text);
        }
    }

    // Lazyload
    $(".lazyload").lazyload({
        effect : "fadeIn",
        container: $("#sidebar")
    });

    if (count !== undefined) {
        var plural = (count > 1 ? 's' : '');
        $('.sidebar-count').text(
            'Affichage de '+count+' annonce'+plural
        );
    }

    // Bind click
    $('#sidebar').on('click', '.pwet', function(event) {
        var i = $(this).data('index');
        google.maps.event.trigger(markers[i], "click");
    });

    // Bind hover
    $('#sidebar').on({
        mouseenter: function() {
            var i = $(this).data('index');
            markers[i].setAnimation(google.maps.Animation.BOUNCE);
        },
        // mouse out
        mouseleave: function () {
            var i = $(this).data('index');
            markers[i].setAnimation(null);
        }
    }, '.pwet');

    // HL first
    panel_highlight(0);
}

function panel_highlight(index) {
    $('#sidebar .pwet').removeClass('active');
    $('#sidebar .pwet[data-index="'+index+'"]').addClass('active');
    var container = $('#sidebar'),
         scrollTo = $('#sidebar .pwet[data-index="'+index+'"]').first();
    // Or you can animate the scrolling:
    container.animate({
        scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
    });
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
            '<div class="media">' +
                '<div class="media-left media-middle">' +
                    '<img class="media-object lazyload" data-original="'+ad.picture+'" alt="'+ad.title+'">' +
                    '<span class="media-number">'+ad.picture_count+'</span>' +
                '</div>' +
                '<div class="media-body">' +
                    '<h3 class="media-heading">'+
                        '<a href="'+ad.url+'" title="'+ad.title+'" target="_blank">' +
                            ad.title+
                        '</a>'+
                    '</h3>' +
                    '<p class="media-supp">'+ad.pro+'</p>' +
                    '<p class="media-supp">'+ad.location+'</p>' +
                    '<p class="media-price">'+ad.price+'</p>' +
                    '<p class="media-date">'+ad.date+'</p>' +
                '</div>' +
            '</div>';

        // Test if current ad has the same lat/lng of another ad
        var found = false;
        for (var j in result) {
            var tmp = result[j];
            // ad matching another one
            if (tmp.latlng.lat == ad.latlng.lat && tmp.latlng.lng == ad.latlng.lng) {
                found = true;
                result[j].ads.push(ad)
                break;
            }
        }
        // If found, we add the pop-up content next to the current one('s)
        if (!found) {
            result.push({
                'ads'   : [ad],
                'latlng': ad.latlng
            });
        }
    }

    // We set class index for each ads now, based of lat/lng
    for (var i in result) {
        var ads = result[i].ads;
        for (var j in ads) {
            ads[j].text = ''+
                '<li class="pwet" data-index="'+i+'">'+ads[j].text+'</li>';
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

