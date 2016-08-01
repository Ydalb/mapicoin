$(document).ready(function() {

    // ===
    // Filters
    // ===
    init_search_filters();

    $('body').on('click', '#toggle', function(event) {
        $('body').toggleClass('toggle');
    })

    // ===
    // Init. google map
    // ===
    map   = initialize_map();

    // ===
    // Focus main input on load
    // ===
    $('.input-url:eq(1)').focus();

    // ===
    // Form submit
    // ===
    $('.form-search').each(function() {
    $(this).on('submit', function(event) {
        event.preventDefault();
        var $form  = $(this);
        var $input = $form.find('.input-url');
        var url    = $input.val();
        // Update all input-url
        if (!url) {
            $form.find('.input-url').focus();
            alert("Veuillez renseigner une URL de recherche leboncoin.fr");
            return false;
        }
        $input.blur();
        $('.input-url').val(url);

        lock_search(true);

        map.initialZoom = false;

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
                    $input.focus();
                    return false;
                }
                if (!data.datas || data.datas.length == 0) {
                    lock_search(false);
                    alert(
                        "Aucune annonce trouvée pour cette recherche." +
                        "\n"+
                        "Si besoin, rendez-vous sur la page 'Comment ça marche' pour plus d'explications."
                    );
                    $input.focus();
                    return false;
                }

                // Remove previous markers
                remove_markers();

                // Update URL
                var tmp = url.replace(/\?/g, '%3F').replace(/&/g, '%26');
                update_browser_url({u: tmp}, false);

                // Group ads by lat/lng
                var datas = regroup_ads(data.datas);

                // Toggle panel
                panel_toggle(true);

                // Update panel + Bind
                panel_update(data, datas);

                // Create and add markers to the map + bind
                add_ads_markers(map, datas);

                // Fit bounds
                map_fit_bounds();

                // ScrollTo the map
                $('html, body').animate({
                    scrollTop:$('#map').offset().top
                    },
                    400,
                    function () {
                        $('#header').hide();
                        $('#map').show();
                        lock_search(false);
                    }
                );
            },
            error: function() {
                lock_search(false);
                alert(
                    "Une erreur est survenue. Veuillez ré-essayer." +
                    "\n"+
                    "Si besoin, rendez-vous sur la page 'Comment ça marche' pour plus d'explications.");
                $input.focus();
            }
        })

    });
    });


    // ===
    // Detect GET parameter and submit if needed
    // ===
    var u = parse_query_strings('u');
    if (u) {
        u = u
            .replace(/%3F/g, '?')
            .replace(/%26/g, '&');
        $('.input-url').val(u);
        $('.form-search').first().submit();
    }
    var distance = parse_query_strings('distance');
    if (distance) {
        set_user_distance(distance);
    }
    var day = parse_query_strings('day');
    if (day) {
        set_user_day(day);
    }

});

/**
 * (un)lock the search form
 */
function lock_search(lock) {
    if (lock) {
        $('.input-submit').button('loading');
        $('.input-url').prop('readonly', true);
        $("body").css("cursor", "progress");
    } else {
        $('.input-submit').button('reset');
        $('.input-url').prop('readonly', false);
        $("body").css("cursor", "default");
    }
}

function panel_toggle(toggle) {
    if (toggle) {
        $('body').addClass('in-search');
        //$('#map').addClass('in-search');
        //$('#overlay').fadeOut();
    } else {
        $('body').removeClass('in-search');
        //$('#map').removeClass('in-search');
        //$('#overlay').fadeIn();
    }
    google.maps.event.trigger(map, "resize");
}

function panel_update(data, datas) {
    var title = data.title;
    // Title + Content + Edit
    $('.sidebar-title').text(title).attr('title', title);
    $('.sidebar-edit-search').attr('href', $('.input-url').first().val());
    $('#sidebar').html('');

    // Update content
    for (var i in datas) {
        var ads = datas[i].ads;
        for (var j in ads) {
            $('#sidebar').append(ads[j].text);
        }
    }

    // Update count
    panel_update_count();
    // Update average
    panel_update_average(data);

    // Lazyload
    $(".lazyload").lazyload({
        effect : "fadeIn",
        container: $("#sidebar")
    });

    // Bind click
    $('#sidebar').on('click', '.pwet', function(event) {
        var i = $(this).data('index');
        google.maps.event.trigger(markers[i], "click");
    });

    // Bind hover
    $('#sidebar').on({
        mouseenter: function() {
            var i = $(this).data('index');
            if (typeof markers[i] == 'undefined') {
                return;
            }
            markers[i].setAnimation(google.maps.Animation.BOUNCE);
        },
        // mouse out
        mouseleave: function () {
            var i = $(this).data('index');
            if (typeof markers[i] == 'undefined') {
                return;
            }
            markers[i].setAnimation(null);
        }
    }, '.pwet');

    // HL first
    panel_highlight(0);
}
function panel_update_count() {
    var count = $('#sidebar').find('.pwet:visible').length;
    var plural = (count > 1 ? 's' : '');
    $('.sidebar-count').text(
        'Affichage de '+count+' annonce'+plural
    );
    $('.sidebar-count').text(
        'Affichage de '+count+' annonce'+plural
    );
}
function panel_update_average(data) {
    if (data.currency.position == 'left') {
        var text = "{1}{0}";
    } else {
        var text = "{0}{1}";
    }
    price = data.average_price;
    text  = text.format(
        price.formatMoney(0, ',', '\''),
        data.currency.symbol
    );
    $('.sidebar-average-price').text(
        "Prix moyen : "+text
    );
}

function panel_highlight(index) {
    $('#sidebar .pwet').removeClass('active');
    $('#sidebar .pwet[data-index="'+index+'"]').addClass('active');
    var container = $('#sidebar'),
         scrollTo = $('#sidebar .pwet[data-index="'+index+'"]').first();
    if (scrollTo.size()) {
        container.animate({
            scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
        });
    }
}
function panel_toggle_item(index, show) {
    var $e = $('#sidebar .pwet[data-index="'+index+'"]');
    if (show) {
        $e.show();
    } else {
        $e.hide();
    }
}



/**
 * Bind les filtres de recherche
 */
function init_search_filters() {
    $('#filter-distance,#filter-day').on('change', function() {
        var value = $(this).val();
        var id    = $(this).attr('id');
        // Update URL
        // var day      = parse_query_strings('day');
        // var distance = parse_query_strings('distance');
        switch (id) {
            case 'filter-day':
                update_browser_url({'day': value}, false);
                set_user_day(value);
                break;
            case 'filter-distance':
                update_browser_url({'distance': value}, false);
                set_user_distance(value);
                break;
            default:
                // Invalid choice
                break;
        }
        update_marker_from_filters();
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
            '<div class="media" data-timestamp="'+ad.timestamp+'">' +
                '<div class="media-left media-middle">' +
                    '<img class="media-object lazyload" data-original="'+ad.picture+'" alt="'+ad.title+'">' +
                    // '<span class="media-number '+ad.picture_count+'">'+ad.picture_count+'</span>' +
                '</div>' +
                '<div class="media-body">' +
                    '<h3 class="media-heading">'+
                        '<a href="'+ad.url+'" title="'+ad.title+'" target="_blank">' +
                            ad.title+
                        '</a>'+
                    '</h3>' +
                    '<p class="media-supp '+ad.pro+'">'+ad.pro+'</p>' +
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



