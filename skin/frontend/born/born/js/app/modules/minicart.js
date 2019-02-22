define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var $html = $('html');
  var popup = $('#popup-minicart');
  var doc = $(document);
  var timeForMinicart = 400; //ms

  var foundHandler = false;

  var MinicartView = Backbone.View.extend({
    initialize: function () {
    },
    bindEvents: function () {
      $('#toggleMiniCartBtn')
        .off('click.minicartEvent')
        .on('click.minicartEvent', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var docEvents = $._data(document, "events").click;

        for (var i = 0; docEvents.length > i; i++) {
          if (docEvents[i].data && docEvents[i].data.name === 'minicart') {
            foundHandler = true;
          }
        }
        if (!foundHandler) {
          doc.on('click', {name: 'minicart'}, documentClick);
        }

        if (!popup.hasClass('skip-active')) {
          popup.addClass('skip-active');
          $html.addClass('_open-basket');
          $('body').addClass('overlay-menu');
          popup.animate({ "right": "0" }, timeForMinicart);
        }
        else {
          popup.animate({ "right": "-425" }, timeForMinicart, function () {
            popup.removeClass('skip-active');
            $('body').removeClass('overlay-menu');
          });
          doc.unbind('click', documentClick);
        }

        function documentClick(e) {
          if (!popup.hasClass('skip-active')) {
            return;
          }
          var target = $(e.target);
          if (target.parents('#popup-minicart').length !== 1) {
            popup.animate({ "right": "-425" }, timeForMinicart,
              function () {
                popup.removeClass('skip-active');
                $('body').removeClass('overlay-menu');
              });
            e.stopPropagation();
            doc.unbind('click', documentClick);
          }
        }
      });
      $('.btn-minicart-toggle')
        .off('click')
        .on('click', function() {
        popup.animate({ "right": "-425" }, timeForMinicart,
          function () {
            popup.removeClass('skip-active');
            $('body').removeClass('overlay-menu');
          });
      });
    },
    init: function () {
      this.bindEvents();
    }
  });
  return new MinicartView();
});
