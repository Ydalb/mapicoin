var _data       = null;
var defaultSort = null;

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
    // Handle demo links
    // ===
    handle_demo_links();

    // ===
    // Handle alert box
    // ===
    handle_alert_boxes();

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
            custom_alert(
                "Oops !",
                "Veuillez renseigner le champ de recherche :<br /><br />" +
                    "<ul>" +
                        "<li>- soit par une adresse de recherche (Ex : <a class='demo-link'>https://www.leboncoin.fr/animaux/offres/pays_de_la_loire/?th=1&q=Cheval&it=1</a>)</li>" +
                        "<li>- soit par une recherche classique (Ex : <a class='demo-link'>Cheval</a>)</li>" +
                    "</ul>",
                "warning",
                {html: true}
            );
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
                    custom_alert("Oops !", data.message, "error");
                    return false;
                }
                if (!data.datas || data.datas.length == 0) {
                    lock_search(false);
                    custom_alert(
                        "Aucune annonce trouvée :-(",
                        "Votre recherche n'a retourné aucun résultat.<br />" +
                            "Si besoin, rendez-vous sur la page <a href='/comment-ca-marche.php'>Comment ça marche</a> " +
                            "pour plus d'explications " +
                            "sur le fonctionnement de <a>Mapicoin</a>",
                        "warning",
                        {html: true}
                    );
                    return false;
                }

                _data = data;

                // Sort data (price/date)
                sort_data();

                // Display legend
                $('#legend').show();

                // Remove previous markers
                remove_markers();

                // Update URL
                var tmp = url.replace(/\?/g, '%3F').replace(/&/g, '%26');
                update_browser_url({u: tmp}, false);

                // Group ads by lat/lng
                // var datas = regroup_ads(data.datas);

                // Toggle panel
                panel_toggle(true);

                // Update panel + Bind
                panel_update(_data);

                // Create and add markers to the map + bind
                add_ads_markers(map, _data.datas);

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
                custom_alert(
                    "Oops !",
                    "Une erreur inconnue sur <a>Mapicoin</a> est survenue.<br />" +
                        "<br />" +
                        "Si le problème persiste, n'hésitez pas à contacter notre équipe via " +
                        "<a target='_blank' href='https://www.facebook.com/mapicoin/'>Facebook</a> ou " +
                        "<a target='_blank' href='https://twitter.com/mapicoin'>Twitter</a>.",
                    "error",
                    {html: true}
                );
            }
        })

    });
    });


    // ===
    // Detect GET parameter and submit if needed
    // ===
    var u = parse_query_strings('u');
    if (u) {
        // u = u
        //     .replace(/%3F/g, '?')
        //     .replace(/%26/g, '&');
        u = decodeURIComponent(u);
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
    var sort = parse_query_strings('sort');
    if (sort) {
        defaultSort = sort;
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

function panel_update(data) {
    // Title + Content + Edit
    $('.sidebar-title').text(data.title).attr('title', data.title);
    $('.sidebar-edit-search').attr('href', $('.input-url').first().val());

    // Update content (ads list)
    panel_update_content();
    // Update count
    panel_update_count();
    // Update average
    panel_update_average(data);

    // // Lazyload
    // $(".lazyload").lazyload({
    //     effect : "fadeIn",
    //     container: $("#sidebar")
    // });

    // Bind click
    $('#sidebar').on('click', '.pwet', function(event) {
        var id = $(this).data('id');
        var m  = get_marker_by_id(id);
        if (m) {
            google.maps.event.trigger(m, "click");
        }
    });

    // Bind hover
    $('#sidebar').on({
        mouseenter: function() {
            var id = $(this).data('id');
            // if (typeof markers[i] == 'undefined') {
            //     return;
            // }
            var m = get_marker_by_id(id);
            if (m) {
                m.setAnimation(google.maps.Animation.BOUNCE);
            }
        },
        // mouse out
        mouseleave: function () {
            var id = $(this).data('id');
            // if (typeof markers[i] == 'undefined') {
            //     return;
            // }
            var m = get_marker_by_id(id);
            if (m) {
                m.setAnimation(null);
            }
        }
    }, '.pwet');

    // HL first
    panel_highlight(0);
}
function panel_update_content() {
    $('#sidebar').html('');
    for (var i in _data.datas) {
        var ad   = _data.datas[i];
        var html = ad_to_html(ad);
        $('#sidebar').append(html);
    }
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

function panel_highlight(id) {
    $('#sidebar .pwet').removeClass('active');
    $('#sidebar .pwet[data-id="'+id+'"]').addClass('active');
    var container = $('#sidebar'),
         scrollTo = $('#sidebar .pwet[data-id="'+id+'"]').first();
    if (scrollTo.size()) {
        container.animate({
            scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
        });
    }
}
function panel_toggle_item(id, show) {
    var $e = $('#sidebar .pwet[data-id="'+id+'"]');
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
    $('#filter-distance,#filter-day,#filter-sort').on('change', function() {
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
            case 'filter-sort':
                update_browser_url({'sort': value}, false);
                defaultSort = value;
                sort_data();
                panel_update_content();
                // Little bug with lazyload, need to trigger
                // $(".lazyload").trigger('appear');
                break;
            default:
                // Invalid choice
                break;
        }
        update_marker_from_filters();
    });
}

/**
 * Gère les événements sur les liens de démonstration
 */
function handle_demo_links() {
    $('body').on('click', '.demo-link', function(event) {
        event.preventDefault();
        // Fill input
        $('.input-url').val($(this).text());
        // Close modal (found nothin' better than simulate a click...)
        $('.sa-confirm-button-container').find('button').click()
        // Submit
        $('#form-search').submit();
    })
}

/**
 * Gère les événements pour pop-up des modals diverses
 */
function handle_alert_boxes() {
    $('body').on('click', '.filter-item.filter-distance.disabled', function() {
        custom_alert(
            "Fonctionnalité désactivée ! ",
            "Cette fonctionnalité est désactivée car il semblerait que " +
                "vous n'avez pas activé la géolocalisation.<br />" +
                "Nous avons besoin de l'approbation de votre navigateur afin de déterminer votre position. ",
            "warning",
            {
                html:true,
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
                confirmButtonText: "Déterminer ma position"
            },
            function () {
                get_user_location();
            }
        );
    })
}


function ad_to_html(ad) {
    if (typeof ad.title == 'undefined') {
        return false;
    }
    return '' +
'<li class="pwet" data-id="'+ad.id+'">' +
    '<div class="media" data-timestamp="'+ad.timestamp+'">' +
        '<div class="media-left media-middle">' +
            '<img class="media-object" src="'+ad.picture+'" alt="'+ad.title+'">' +
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
    '</div>' +
'</li>';
}

function sort_data() {
    var key = null;
    switch (defaultSort) {
        case 'price': key = 'price_raw'; break;
        case 'date':  key = 'timestamp'; break;
        default:      return false;      break;
    }
    // We need to transform object into array to sort it
    var sortable = [];
    for (var i in _data.datas) {
        sortable.push(_data.datas[i]);
    }
    // console.log(sortable);
    sortable.sort(
        function(a, b) {
            var x = a[key]; var y = b[key];
            if (key == 'price_raw') {
                return x - y;
            } else {
                return y - x;
            }
            // return ((x < y) ? -1 : ((x > y) ? 1 : 0));
        }
    )
    _data.datas = {};
    for (var i in sortable) {
        _data.datas[i] = sortable[i];
    }
    console.log('sorted', key, sortable);
}

/**
 * Used to browse all ads and group them by location (lat/lng) in
 * order to have 1 marker for multiple ads
 */
// function regroup_ads(datas) {

//     var result = [];

//     for (var i in datas) {

//         var ad      = datas[i];
//         ad['count'] = 1;
//         ad['text']  = ad_to_html(ad);

//         // Test if current ad has the same lat/lng of another ad
//         var found = false;
//         for (var j in result) {
//             var tmp = result[j];
//             // ad matching another one
//             if (tmp.latlng.lat == ad.latlng.lat && tmp.latlng.lng == ad.latlng.lng) {
//                 found = true;
//                 result[j].ads.push(ad)
//                 break;
//             }
//         }
//         // If found, we add the pop-up content next to the current one('s)
//         if (!found) {
//             result.push({
//                 'ads'   : [ad],
//                 'latlng': ad.latlng
//             });
//         }
//     }

//     // We set class index for each ads now, based of lat/lng
//     for (var i in result) {
//         var ads = result[i].ads;
//         for (var j in ads) {
//             ads[j].text = ''+
//                 '<li class="pwet" data-index="'+i+'">'+ads[j].text+'</li>';
//         }
//     }

//     return result;
// }



