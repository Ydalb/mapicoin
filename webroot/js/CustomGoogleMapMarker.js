/* Constructor */
function CustomMarker(latlng, map, args) {
    this.latlng = latlng;
    this.args   = args;
    this.setMap(map);
}

CustomMarker.prototype = new google.maps.OverlayView();

CustomMarker.prototype.draw = function() {
    var self = this;
    var div  = this.div;
    if (!div) {
        // Main div (= marker)
        div = this.div = document.createElement('div');
        div.style.position = 'absolute';
        div.classList = 'marker-container';
        div.innerHTML = '' +
'<div class="marker">' +
    '<span>'+this.args.price+'</span>' +
'</div>';
        if (this.args.visited) {
            this.setVisited(true);
        }
        // Click event
        google.maps.event.addDomListener(div, "click", function(event) {
            // google.maps.event.trigger(self, "click");
            if (is_geolocated) {
                calc_distance_to_marker(self.args.id);
            }
            set_cookie(self.args.id, 'visited');
            self.setVisited(true);
            currentActiveMarkerId = self.args.id;
            panel_highlight(self.args.id);
            // Open sidebar if mobile
            if (is_mobile()) {
                $('body').removeClass('toggle');
            }
        });
        var panes = this.getPanes();
        panes.overlayImage.appendChild(div);
    }

    var point = this.getProjection().fromLatLngToDivPixel(this.latlng);

    if (point) {
        div.style.left = (point.x - 10) + 'px';
        div.style.top = (point.y - 20) + 'px';
    }
};

CustomMarker.prototype.remove = function() {
    if (this.div) {
        this.div.parentNode.removeChild(this.div);
        this.div = null;
    }
};

CustomMarker.prototype.getPosition = function() {
    return this.latlng;
};
CustomMarker.prototype.setPosition = function(latlng) {
    this.latlng = latlng;
    return this;
};
CustomMarker.prototype.isHovered = function(hovered) {
    if (this.div) {
        if (hovered) {
            this.args.zIndex      = this.div.style.zIndex;
            this.div.style.zIndex = 9000;
            this.div.classList.add('hover');
        } else {
            this.div.style.zIndex = this.args.zIndex;
            this.div.classList.remove('hover');
        }
    }
};
CustomMarker.prototype.setVisited = function(visited) {
    this.args.visited = visited;
    if (this.div) {
        this.div.classList.add('visited');
    }
    return this;
};
CustomMarker.prototype.setVisible = function(visible) {
    if (this.div) {
        if (visible)  {
            this.div.style.visibility = 'visible';
        } else {
            this.div.style.visibility = 'hidden';
        }
    }
    return this;
};
CustomMarker.prototype.getVisible = function() {
    if (this.div) {
        if (this.div.style.visibility == 'visible')  {
            return true;
        }
        return false;
    }
    return false;
};