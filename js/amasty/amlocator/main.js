var AmLocator = Class.create();
AmLocator.prototype = {
    initialize: function(ajaxCallUrl,useGeo) {
        this.url = ajaxCallUrl;
        this.useGeo = useGeo;
    },

    goHome: function(){
        console.log(window.location);
        window.location.href = window.location.pathname;
    },

    navigateMe: function(){

        if ( (navigator.geolocation) && (this.useGeo==1) ) {
            var self = this;
            navigator.geolocation.getCurrentPosition( function(position) {
                self.makeAjaxCall(position);
            }, this.navigateFail );
        }else{
            this.makeAjaxCall();
        }
    },

    navigateFail: function(error){
        document.getElementById("cancel-please-wait").style.display = "none";
    },

    getQueryVariable: function(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
    },

    makeAjaxCall: function(position) {
        var self = this;
        document.getElementById("cancel-please-wait").style.display = "block";
        if ( (position != "") && (typeof position!=="undefined")){

            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            new Ajax.Request(this.url,
                {
                    method: 'POST',
                    parameters: {"lat": lat, "lng": lng},
                    onSuccess: function(transport) {
                        if (200 == transport.status) {
                            var response = transport.responseText.evalJSON();
                            document.getElementById("amlocator_left").innerHTML = response.block;
                            self.Amastyload(response);
                        }
                    }
                });
        }else{
            new Ajax.Request(this.url,
                {

                    method: 'POST',
                    parameters: {"sort":"distance", "lat": lat, "lng": lng},
                    onSuccess: function(transport) {
                        if (200 == transport.status) {
                            var response = transport.responseText.evalJSON();
                            document.getElementById("amlocator_left").innerHTML = response.block;
                            self.Amastyload(response);
                        }
                    }
                });
        }

    },
    closeAllInfoWindows: function () {

        var spans = document.getElementById('amlocator_left').getElementsByTagName('span');

        for(var i = 0, l = spans.length; i < l; i++){

            spans[i].className = spans[i].className.replace(/\active\b/,'');
        }

        if (typeof this.marker !=="undefined"){
            for (var i=0;i<this.marker.length;i++) {
                this.marker[i]['infowindow'].close();
            }
        }

    },

    createMarker: function(lat, lon, html, marker) {
        if (marker) {
            var marker = {
                url: marker,
                size: new google.maps.Size(48, 48),
                scaledSize: new google.maps.Size(48, 48)
            };
            var newmarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lon),
                map: this.map,
                icon: marker
            });
        } else {
            var newmarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lon),
                map: this.map
            });
        }

        newmarker['infowindow'] = new google.maps.InfoWindow({
            content: html
        });
        var self = this;
        google.maps.event.addListener(newmarker, 'click', function() {
            self.closeAllInfoWindows();
            this['infowindow'].open(self.map, this);
        });

        this.marker.push(newmarker);
    },

    initializeMap: function() {
        this.infowindow = [];
        this.marker = [];
        var myOptions = {
            zoom: 9,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        this.map = new google.maps.Map(document.getElementById("amlocator-map-canvas"), myOptions);
    },

    replaceIfStatement: function(text,value,template){
        var patt = new RegExp("\{\{if"+template+"\}\}([\\s\\S]*)\{\{\/\if"+template+"}\}","g");
        var cuteText = patt.exec(text);
        if (cuteText!=null ){
            if (value=="" || value==null){
                text = text.replace(cuteText[0], '');
            }else{
                var finalText = this.replaceAll(cuteText[1], '{{'+template+'}}', value);
                text = text.replace(cuteText[0], finalText);
            }

            return text;
        }
        return text;
    },

    replaceAll: function(str, find, replace) {
        return str.replace(new RegExp(find, 'g'), replace);
    },

    processLocation: function(locations) {
        var template = baloonTemplate.baloon; // document.getElementById("amlocator_window_template").innerHTML;
        var curtemplate = "";
		var bounds  = new google.maps.LatLngBounds();

        if (locations.totalRecords == 0) {
            this.map.setCenter(new google.maps.LatLng(document.getElementById("am_lat").value, document.getElementById("am_lng").value));
            return false;
        }

        for (var i = 0; i < locations.totalRecords; i++) {

            curtemplate = template;
            curtemplate = curtemplate.replace("{{name}}", locations.items[i].name);
            curtemplate = curtemplate.replace("{{city}}", locations.items[i].city);
            curtemplate = curtemplate.replace("{{zip}}", locations.items[i].zip);
            curtemplate = curtemplate.replace("{{address}}", locations.items[i].address);

            curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].state, 'state');
            curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].email, 'email');
            curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].phone, 'phone');
            curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].website, 'website');

            if  (typeof locations.items[i].description != 'undefined' && locations.items[i].description != null) {
                curtemplate = curtemplate.replace("{{description}}", locations.items[i].description);
            } else {
                curtemplate = curtemplate.replace("{{description}}", "");
            }


            if (locations.items[i].photo != "") {
                curtemplate = curtemplate.replace("{{photo}}", locations.items[i].photo);
            } else {
                curtemplate = curtemplate.replace(/<img[^>]*>/g, "");
            }

            if (locations.items[i].marker != "") {
                markerImage = amMediaUrl + locations.items[i].marker;
            } else {
                markerImage = "";
            }

            this.createMarker(locations.items[i].lat, locations.items[i].lng,  curtemplate, markerImage);
			var loc = new google.maps.LatLng(this.marker[i].position.lat(), this.marker[i].position.lng());
			bounds.extend(loc);
        }

        this.gotoPoint(1);
        this.map.panToBounds(bounds);
        this.map.fitBounds(bounds);
    },
    gotoPoint: function(myPoint,element){
        this.closeAllInfoWindows();
        if (typeof element!=="undefined"){
            element.className = element.className + " active";
        }else{
            var spans = document.getElementById('amlocator_left').getElementsByTagName('span');
            spans[0].className = spans[0].className + "active";
        }
        this.map.setCenter(new google.maps.LatLng( this.marker[myPoint-1].position.lat(), this.marker[myPoint-1].position.lng()));
        this.map.setZoom(20);
        this.marker[myPoint-1]['infowindow'].open(this.map, this.marker[myPoint-1]);
    },
    Amastyload: function(locations) {
        document.getElementById("cancel-please-wait").style.display = "none";
        this.initializeMap();
        this.processLocation(locations);

        var markerCluster = new MarkerClusterer(this.map, this.marker);

        this.geocoder = new google.maps.Geocoder();


        var address = document.getElementById('amlocator-search');
        var autocomplete = new google.maps.places.Autocomplete(address);

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();

            document.getElementById('am_lat').value = place.geometry.location.lat();
            document.getElementById('am_lng').value = place.geometry.location.lng();
        });

    },
    sortByFilter: function(){

        var e = document.getElementById("amlocator-radius");
        var radius = e.options[e.selectedIndex].value;
        var lat = document.getElementById("am_lat").value;
        var lng = document.getElementById("am_lng").value;

        if (!lat || !lng){
            alert('Please fill Current Location field');
            return false;
        }

        document.getElementById("cancel-please-wait").style.display = "block";

        var self = this;
        new Ajax.Request(self.url,
            {
                method: 'POST',
                parameters: {"lat": lat, "lng": lng , "radius": radius },
                onSuccess: function(transport) {
                    if (200 == transport.status) {
                        var response = transport.responseText.evalJSON();
                        document.getElementById("amlocator_left").innerHTML = response.block;
                        self.Amastyload(response);

                    }
                    document.getElementById("cancel-please-wait").style.display = "none";
                }
            }
        );
    }


};