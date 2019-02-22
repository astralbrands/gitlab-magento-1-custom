define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";
  var AboutUsView = Backbone.View.extend({
    initialize: function () {
      try {
        var aboutUsSlider = jQuery('.bxVideoCarousel').bxSlider({
          nextText: '',
          swipeThreshold: 100,
          prevText: ''
        });
      } catch (err) {
        console.log(err);
      }

      // var svgImg = $('<img class="icon-img svg-file" src="/skin/frontend/born/born/images/PUR_logo.svg">');
      // $('.non-svg-file').replaceWith(svgImg);

      $('img.svg-file').each(function () {
        var img = $(this);
        var imgURL = img.prop('src');
        var isBlack = img.parent().hasClass('black');
        var isWhite = img.parent().hasClass('white');

        $.get(imgURL, function (data) {
          // Get the SVG tag, ignore the rest
          var $svg = $(data).find('svg');

          // change the color
          if (isBlack) {
            $svg.find('g *').attr('fill', '#000');
          }
          if (isWhite) {
            $svg.find('g *').attr('fill', '#fff');
          }

          var s = new XMLSerializer().serializeToString($svg[0]);
          img.prop('src', "data:image/svg+xml;base64," + window.btoa(s));
        });

      });
    }
  });
  return new AboutUsView();
});
