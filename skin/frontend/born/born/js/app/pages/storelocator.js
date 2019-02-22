define([
  'jquery',
  'backbone',
  'lodash',
  'infobox',
  'markerwithlabel'
], function ($, Backbone, _, infobox, markerwithlabel) {

  'use strict';

  var StorelocatorView = Backbone.View.extend({
    initialize: function () {

      var globalData;

      function findIndexInputInForm(array, key) {
        return _.findIndex(array, function (elem, index) {
          if (elem.name == key) {
            return index;
          }
        })
      }

      var map = new google.maps.Map(document.getElementById('map-canvas'), {
        styles: [{
          featureType: "poi",
          elementType: "labels",
          stylers: [{visibility: "off"}]
        }],
        center: {
          lat: 35.55583690,
          lng: -119.74605270
        },
        zoom: 7
      });

      $('#storelocator-form').on('submit', function (event) {
        event.preventDefault();

        $('.storelocator').addClass('show-map');
        if (window.innerWidth <= 768) {
          $('.storelocator').addClass('map-view');
        }

        var parsedParams = $(this).serializeArray();

        var model = {
          postal_code: {value: $('#inputzipcode').val()} || {},
          product: parsedParams[findIndexInputInForm(parsedParams, 'product')] || {},
          distance: parsedParams[findIndexInputInForm(parsedParams, 'distance')] || {}
        };

        var address = $('#inputzipcode').val();
        var distance = model.distance.value;
        var product = model.product.value;

        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function (results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            var lat = results[0].geometry.location.lat();
            var lng = results[0].geometry.location.lng();
            getStores(lat, lng);
          } else {
            console.log("Geocode was not successful for the following reason: " + status);
            nothingFoundParse(model);
          }
        });

        var isRealSearchKey = findIndexInputInForm(parsedParams, 'isRealSearch');

        function getStores(lat, lng) {
          var url = parsedParams[isRealSearchKey].value === 'true' ? '/store-locator/index/search/lat/' + lat + '/lng/' + lng + '/distance/' + distance + '/product/' + product
            : '/store-locator/index/search/lat/36.778261/lng/-119.4179324/distance/1000/product/';

          $.ajax({
            type: 'GET',
            url: url,
            success: function (data) {
              clearResults();
              if (data.length) {
                globalData = data;
                model.storesLength = data.length;

                includePins(data);
                includeSearchTitle(model);
                includeStores(data);
              }
              else {
                nothingFoundParse(model);
              }
            },
            error: function (err) {
              model.err = err;
              nothingFoundParse(model);
            }
          });
        }
      });

      $('.link-edit-search').on('click', function () {
        $('.storelocator').removeClass('show-map');
        $('.mobile-search-result h2').remove();
      });

      $('.btn-map').on('click', function (e) {
        e.preventDefault();
        $('.storelocator').addClass('map-view');
        $(this).addClass('current').siblings().removeClass('current');
      });
      $('.btn-stores-list').on('click', function (e) {
        e.preventDefault();
        $('.storelocator').removeClass('map-view');
        $(this).addClass('current').siblings().removeClass('current');
      });

      var markerArr = [],
        $contentString,
        previousMarkerIndex;

      function toggleSpin() {
        if (previousMarkerIndex != undefined) {
          markerArr[previousMarkerIndex].infoBox.close();
          //$('.icon-map_pin_' + (previousMarkerIndex + 1)).removeClass('icon-map_pin_' + (previousMarkerIndex + 1) + '_filled');
          var srcNotFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '.svg';
          var srcFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '_filled.svg';
          $('img[src="' + srcFilled + '"]').attr('src', srcNotFilled);
          $('.store-list-store').removeClass('dark');
        }
        previousMarkerIndex = undefined;
      }

      function includePins(data) {

        var bounds = new google.maps.LatLngBounds();

        google.maps.event.addListener(map, "click", function (event) {
          toggleSpin();
        });

        // more readble
        var html = '' +
          '<div class="store-baloon">' +
            '<div class="store-baloon-title">{{company}}</div>' +
            '<div class="store-baloon-address">{{street}}</div>' +
            '<div class="store-baloon-city">{{city}},</div> ' +
            '<div class="store-baloon-code">{{postal_code}} {{country}}</div>' +
            '<div class="store-baloon-phone">{{phone}}</div>' +
          '</div>';

        for (var i = 0, max = data.length; i < max; i++) {

          $contentString = _.template(html);
          markerArr[i] = {};
          markerArr[i].marker = new MarkerWithLabel({
            position: {
              lat: +data[i].lat,
              lng: +data[i].lng
            },
            draggable: false,
            raiseOnDrag: false,
            icon: ' ',
            map: map,
            //labelContent: '<span class="icon-map_pin_' + (i + 1) + '"></span>',
            labelContent: '<img class="map-pins" src="/skin/frontend/born/born/images/map_pins/map_pin_' + (i + 1) + '.svg' + '"/>',
            labelAnchor: new google.maps.Point(20, 30),
            labelClass: "marker-label"
          });

          var myOptions = {
            content: $contentString(data[i]),
            alignBottom: true,
            pixelOffset: new google.maps.Size(-120, -40),
            closeBoxURL: ''
          };

          markerArr[i].infoBox = new InfoBox(myOptions);

          bounds.extend(markerArr[i].marker.position);

          (function (i) {
            google.maps.event.addListener(markerArr[i].marker, "click", function () {
              toggleSpin();
              previousMarkerIndex = i;
              //$('.icon-map_pin_' + (i + 1)).addClass('icon-map_pin_' + (i + 1) + '_filled');
              var srcNotFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '.svg';
              var srcFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '_filled.svg';
              $('img[src="' + srcNotFilled + '"]').attr('src', srcFilled);
              markerArr[i].infoBox.open(map, this);
              $($('.store-list-store')[i]).addClass('dark');
            });
          })(i);

        }

        map.fitBounds(bounds);
      }

      map.addListener('bounds_changed', function () {
        if (previousMarkerIndex != undefined) {
          //$('.icon-map_pin_' + (previousMarkerIndex + 1)).addClass('icon-map_pin_' + (previousMarkerIndex + 1) + '_filled');
          var srcNotFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '.svg';
          var srcFilled = '/skin/frontend/born/born/images/map_pins/map_pin_' + (previousMarkerIndex + 1) + '_filled.svg';
          $('img[src="' + srcNotFilled + '"]').attr('src', srcFilled);
        }
      });

      function includeSearchTitle(model) {
        var totalHtml = '' +
          '<h2 class="searchTitle">{{storesLength}} Results for ' +
          'Zip Code “{{postal_code.value}}”, Distance “{{distance.value}}”</h2>';
        var compiletotalHtml = _.template(totalHtml);

        if (window.innerWidth <= 768) {
          $('.mobile-search-result').prepend(compiletotalHtml(model));
        } else {
          $('.stores').prepend(compiletotalHtml(model));
        }
      }

      function includeStores(stores) {
        var $list = $('<div class="store-list">').appendTo('.stores');

        var htmlBlock = '' +
          '<div class="store-list-store" data-is-elite="{{store.is_elite}}">' +
            '<div class="store-list-inner">' +
              '<div class="store-list-elite"></div>' +
              '<div class="store-list-step">{{index}}</div>' +
              '<div class="store-list-title">{{store.company}}</div>' +
              '<div class="store-list-text">{{store.street}}</div>' +
              '<div class="store-list-text">{{store.city}}, {{store.postal_code}} {{store.country}}</div>' +
              '<div class="store-list-text">{{store.phone}}</div>' +
              '<a href="javascript:void(0)" class="store-list-btn">Directions</a>' +
              '<a href="{{store.website}}" class="store-list-link">{{store.website}}</a>' +
            '</div>' +
          '</div>';

        for (var i = 0, max = stores.length; i < max; i++) {
          var compileBlock = _.template(htmlBlock);
          var parsed = $(compileBlock({store: stores[i], index: i + 1}));
          $list.append(parsed);
          (function (i) {
            parsed.find('.store-list-btn').on('click', i, function (e) {
              toggleSpin();
              previousMarkerIndex = i;
              markerArr[e.data].infoBox.open(map, markerArr[e.data].marker);
              $('.icon-map_pin_' + (i + 1)).addClass('icon-map_pin_' + (i + 1) + '_filled');
              $(this).parent().parent().addClass('dark');
              map.panTo({lat: +stores[i].lat, lng: +stores[i].lng - (8 / (1.5 * map.getZoom()))});
              if (window.innerWidth <= 768) {
                $('html, body').animate({scrollTop: 200});
                $('.storelocator').addClass('map-view');
                $('.btn-map').addClass('current').siblings().removeClass('current');
              } else {
                $('html, body').animate({scrollTop: 0});
              }
            });
          })(i);
        }

      }

      function clearResults() {
        $('.searchTitle').remove();
        $('.store-list').remove();
        $('.store-baloon').remove();
        $('.marker-label').remove();
        markerArr = [];
      }

      function nothingFoundParse(model, err) {
        clearResults();

        if (!err) {
          var html = '' +
            '<h2 class="searchTitle">Nothing found for ' +
            'Zip Code “{{postal_code.value}}”, distance “{{distance.value}}”</h2>';
        }
        else {
          console.log(err);
          return false;
        }

        var compiletotalHtml = _.template(html);
        $('.stores').prepend(compiletotalHtml(model));
      }


    }
  });

  return new StorelocatorView();
});
