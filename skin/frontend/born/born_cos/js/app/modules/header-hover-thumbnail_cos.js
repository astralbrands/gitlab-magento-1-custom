define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";
  var HeaderHoverThumbnailView = Backbone.View.extend({
    initialize: function () {
    },
    bindEvents: function () {
      var $categoryThumbnail;

      var body = $('body');
      var menuBreakPoint = 1150; //px

      $('li.level2').on('mouseenter.submenuImage', function () {
        var $that = $(this);
        var $hoverImg = $that.find('.hover-thumbnail img');
        var $levelTopMenu = $that.parents('.level0');
        $categoryThumbnail = $levelTopMenu.find('.category-thumbnail .thumbnail-image01 img');
        $categoryThumbnail.attr('default-src', $categoryThumbnail.attr('src'));
        if (body.hasClass('cos-b2c') && (window.innerWidth > menuBreakPoint)) {
          if ($hoverImg.length) {
            $categoryThumbnail.attr('src', $hoverImg.attr('src'));
          } else {
            $categoryThumbnail.attr('src', $categoryThumbnail.attr('default-src'));
          }
        }
      })
        .on('mouseleave.submenuImage', function () {
          if (body.hasClass('cos-b2c') && (window.innerWidth > menuBreakPoint)) {
            $categoryThumbnail.attr('src', $categoryThumbnail.attr('default-src'));
          }
        });
    },
    init: function () {
      this.bindEvents();
    }
  });
  return new HeaderHoverThumbnailView();
});
