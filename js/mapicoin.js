$(document).ready(function() {

    $('#input-url').focus();

    $('body').on('click', '#new-search', function(event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop:0
            },
            400,
            function () {
                $('#new-search').hide();
            }
        );
    })

    // Form submit
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
            url:      '/leboncoin-ajax.php',
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
                    alert("Aucune annonce trouvée. Veuillez essayer un autre lien de recherche.");
                    lock_search(false);
                    return false;
                }

                // On regroupe les annondes
                console.log(data.datas);
                var datas = regroup_annonce(data.datas);
                console.log(datas);
                var map   = initialize_map();
                add_annonces_markers(map, datas);
                // ScrollTo
                $('html, body').animate({
                    scrollTop:$('#map').offset().top
                    },
                    400,
                    function () {
                        $('#new-search').show();
                    }
                );

                lock_search(false);
            },
            error: function() {
                alert("Une erreur est survenue. Veuillez ré-essayer.");
                lock_search(false);
            }
        })

    });

});

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

function regroup_annonce(datas) {

    var result = [];

    for (var i in datas) {

        var annonce      = datas[i];
        annonce['count'] = 1;
        annonce['text']  = '' +
            '<a href="'+annonce.url+'" title="'+annonce.title+'" target="_blank" class="list_item">' +
                '<div class="item_image">' +
                    '<span class="item_imagePic">' +
                        '<img src="'+annonce.picture+'">' +
                    '</span>' +
                '</div>' +
                '<section class="item_infos">' +
                    '<h2 class="item_title">'+annonce.title+'</h2>' +
                    '<p class="item_supp">'+annonce.pro+'</p>' +
                    '<p class="item_supp">'+annonce.location+'</p>' +
                    '<h3 class="item_price">'+annonce.price+'</h3>' +
                    '<aside class="item_absolute">' +
                        '<p class="item_supp">'+annonce.date+'</p>' +
                    '</aside>' +
                '</section>' +
            '</a>';

        var found = false;
        for (var j in result) {
            var tmp = result[j];
            if (tmp.latlng.lat == annonce.latlng.lat && tmp.latlng.lng == annonce.latlng.lng) {
                found = true;
            }
        }
        if (found) {
            result[j].text  += annonce.text;
            result[j].count += 1;
        } else {
            result.push(annonce);
        }
    }

    return result;
}

function add_annonces_markers(map, annonces) {
    //create empty LatLngBounds object
    var bounds     = new google.maps.LatLngBounds();
    var infowindow = new google.maps.InfoWindow({
        content: ""
    });

    for (var index in annonces) {

        var annonce = annonces[index];

        var marker  = new google.maps.Marker({
            map:      map,
            position: annonce.latlng,
            title:    annonce.title,
            label:    annonce.count > 1 ? '+' : null
        });

        bind_info_window(marker, map, infowindow, annonce.text);
        //extend the bounds to include each marker's position
        bounds.extend(marker.position);

    }

    //now fit the map to the newly inclusive bounds
    map.fitBounds(bounds);
}

function bind_info_window(marker, map, infowindow, description) {
    marker.addListener('click', function() {
        infowindow.setContent(description);
        infowindow.open(map, this);
    });
}

function initialize_map() {
  var myLatLng = {lat: 47.351, lng: 3.392};
  var element = document.getElementById('map');
  element.style.display = 'block';
  // Create a map object and specify the DOM element for display.
  var map = new google.maps.Map(element, {
    center: myLatLng,
    scrollwheel: true,
    zoom: 6
  });

  return map;
}
