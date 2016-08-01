function set_cookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function get_cookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}


/**
 * Affiche une modal trop stylaÿ
 */
function custom_alert(title, message, type, opts, callback) {
    var basicOpts = {
        title: title,
        text: message,
        type: type,
        html: false,
        confirmButtonColor:"#f56b2a",
        confirmButtonText: "J'ai compris !",
        cancelButtonText: "Annuler"
    }
    opts = $.extend({}, basicOpts, opts);
    if (callback) {
        swal(opts, callback);
    } else {
        swal(opts);
    }
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


/**
 * Detect mobile use
 */
function is_mobile() {
    if( navigator.userAgent.match(/Android/i)
    || navigator.userAgent.match(/webOS/i)
    || navigator.userAgent.match(/iPhone/i)
    || navigator.userAgent.match(/iPad/i)
    || navigator.userAgent.match(/iPod/i)
    || navigator.userAgent.match(/BlackBerry/i)
    || navigator.userAgent.match(/Windows Phone/i)
    ){
        return true;
    }
    return false;
}



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

