
var QueryString = function () {
  // This function is anonymous, is executed immediately and
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair  = vars[i].split("=");
    var key   = pair.shift();
    var value = pair.join('=');
        // If first entry with this name
    if (typeof query_string[key] === "undefined") {
      query_string[key] = decodeURIComponent(value);
        // If second entry with this name
    } else if (typeof query_string[key] === "string") {
      var arr = [ query_string[key],decodeURIComponent(value) ];
      query_string[key] = arr;
        // If third or later entry with this name
    } else {
      query_string[key].push(decodeURIComponent(value));
    }
  }
  return query_string;
}();
String.prototype.format = function () {
        var args = [].slice.call(arguments);
        return this.replace(/(\{\d+\})/g, function (a){
            return args[+(a.substr(1,a.length-2))||0];
        });
};
Number.prototype.formatMoney = function(c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };



$(document).ready(function() {

    // ===
    // Filters
    // ===
    init_search_filters();

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
            $('#input-url').focus();
            alert("Veuillez renseigner une URL de recherche leboncoin.fr");
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
                    alert(
                        "Aucune annonce trouvée pour cette recherche." +
                        "\n"+
                        "Si besoin, rendez-vous sur la page 'Comment ça marche' pour plus d'explications."
                    );
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
                        $('#new-search').show();
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
            }
        })

    });


    // ===
    // Detect GET parameter and submit if needed
    // ===
    var u = parse_query_strings('u');
    if (u) {
        u = u
            .replace(/%3F/g, '?')
            .replace(/%26/g, '&');
        $('#input-url').val(u);
        $('#form-search').submit();
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

function panel_update(data, datas) {
    var title = data.title;
    // Title + Content + Edit
    $('.sidebar-title').text(title).attr('title', title);
    $('.sidebar-edit-search').attr('href', $('#input-url').val());
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
        console.log(scrollTo);
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
                    '<span class="media-number '+ad.picture_count+'">'+ad.picture_count+'</span>' +
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


/**
 * Change l'URL du navigateur
 * datas est un tableau clé => valeur (paramètres GET)
 * si reset = true, on repart sur une nouvelle URL
 * sinon, on prends les paramètres actuels et on surchage avec data
 */
function update_browser_url(data, reset) {
    // Get current GET parameters if reset = false
    var gets = [];
    if (reset == false) {
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
                tmp = item.split("=");
                var k = tmp.shift();
                var v = tmp.join('=');
                gets[k] = v;
            }
        );
    }
    // On surchage les paramètres GET
    for (var i in data) {
        gets[i] = data[i];
    }
    // On reconstruit l'URL
    var url       = '';
    var ampersand = false;
    for (var i in gets) {
        if (gets[i] != '') {
            url      += (ampersand ? '&' : '')+i+'='+gets[i];
            ampersand = true;
        }
    }
    // On met à jour le navigateur
    window.history.pushState("", "", "/?"+url);
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
            if (tmp[0] === val || typeof val == 'undefined') {
                tmp.shift();
                result = tmp.join('=');
            }
        }
    );
    return result;
}


