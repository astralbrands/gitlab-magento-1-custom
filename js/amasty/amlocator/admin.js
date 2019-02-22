Event.observe(window, 'load', function(){
    window.amLocatorObj = new amLocator();

    Event.observe( document.getElementById("location_tabs_custom_section") , "click", function (event) {
        window.amLocatorObj.displayByLatLng();
        Event.stop(event);
    });

    Event.observe( document.getElementById("lat") , "keyup", function (event) {
        document.getElementById("lat").value = document.getElementById("lat").value.replace(",",".");

        window.amLocatorObj.deleteMarkers();
        window.amLocatorObj.displayByLatLng();
        Event.stop(event);
    });

    Event.observe( document.getElementById("lng") , "keyup", function (event) {
        document.getElementById("lng").value = document.getElementById("lng").value.replace(",",".");
        window.amLocatorObj.deleteMarkers();
        window.amLocatorObj.displayByLatLng();
        Event.stop(event);
    });

});





var amLocator = new Class.create();

amLocator.prototype = {

    mapLoad: function(){

    },

    // Extract "GET" parameters from a JS include querystring
    getParams: function(script_name) {
        // Find all script tags
        var scripts = document.getElementsByTagName("script");

        // Look through them trying to find ourselves
        for(var i=0; i<scripts.length; i++) {
            if(scripts[i].src.indexOf("/" + script_name) > -1) {
                // Get an array of key=value strings of params
                var pa = scripts[i].src.split("?").pop().split("&");

                // Split each key=value into array, the construct js object
                var p = {};
                for(var j=0; j<pa.length; j++) {
                    var kv = pa[j].split("=");
                    p[kv[0]] = kv[1];
                }
                return p;
            }
        }

        // No scripts match
        return {};
    },

    initialize: function(){
        this.markers = [];
        var script = document.createElement('script');
        script.type = 'text/javascript';

        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.17.exp&' +
            'callback=amLocatorObj.mapLoad' + amastyGoogleApiKey;
        document.body.appendChild(script);
    },

    displayByLatLng: function(){
        document.getElementById("map-canvas").style.display = "block";
        var mapOptions = {
            zoom: 4
        };

        if (!this.map)
            this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        var lat = document.getElementById('lat').value;
        var lng = document.getElementById('lng').value;
        if (document.getElementById('marker').getAttribute('value')) {
            var markerImage = document.getElementById('marker').getAttribute('value');
        }
        if ( lat!="" && lng!="" && markerImage) {
            var myLatlng = new google.maps.LatLng(lat, lng);
            this.map.setCenter(myLatlng);
            var markerImage = {
                url: markerImage,
                size: new google.maps.Size(48, 48),
                scaledSize: new google.maps.Size(48, 48)
            };
            var marker = new google.maps.Marker({
                map: this.map,
                position: myLatlng,
                icon: markerImage
            });
        } else if (!markerImage) {
            var myLatlng = new google.maps.LatLng(lat, lng);
            this.map.setCenter(myLatlng);
            var marker = new google.maps.Marker({
                map: this.map,
                position: myLatlng
            });
        } else {
            var myLatlng = new google.maps.LatLng(-34.397,150.644);
            this.map.setCenter(myLatlng);
        }
        this.markers.push(marker);
        return true;
    },
    deleteMarkers: function() {
        for (var i = 0; i < this.markers.length; i++) {
            this.markers[i].setMap();
        }
        this.markers = [];
    },

    display: function(){
        var e = document.getElementById("country");
        var country = e.options[e.selectedIndex].text;

        var city = document.getElementById('city').value;
        var zip = document.getElementById('zip').value;
        var address = document.getElementById('address').value;

        address = country +','+ city+','+zip+','+address;

        geocoder = new google.maps.Geocoder();

        document.getElementById("map-canvas").style.display = "block";
        var mapOptions = {
            zoom: 4
        };

        if (!this.map)
            this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

        self = this;
        this.deleteMarkers();
        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                self.map.setCenter(results[0].geometry.location);
                document.getElementById('lat').value = results[0].geometry.location.lat();
                document.getElementById('lng').value = results[0].geometry.location.lng();
                var marker = new google.maps.Marker({
                    map: self.map,
                    position: results[0].geometry.location
                });
                self.markers.push(marker);

            }else{
                window.amLocatorObj.displayByLatLng();
            }
        });
    }
}