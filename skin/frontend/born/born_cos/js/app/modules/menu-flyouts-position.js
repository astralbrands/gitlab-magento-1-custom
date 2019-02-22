define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  var menuFlyoutsPosition;

  "use strict";
  var MenuFlyoutsPositionView = Backbone.View.extend({
    initialize: function () {
      menuFlyoutsPosition = this;
    },
    setPadding: function ($level0MenuActive, mobileBreakPoint) {

      var $level0Submenu = $level0MenuActive.find('.level0');
      var offsetLeft = $level0MenuActive.offset().left;
      var paddingLink = $level0MenuActive.find('a.level-top').css('padding-left') || $level0MenuActive.find('span').css('padding-left');
      var newValue = offsetLeft + parseInt(paddingLink, 10);
      var offsetRight = window.innerWidth - offsetLeft - $level0MenuActive.width();

      if (window.innerWidth > mobileBreakPoint) {
        if ($level0Submenu) {
          if (!$level0MenuActive.hasClass('last')) {
            $level0Submenu.css('padding-left', newValue);
            if ($level0MenuActive.hasClass('first')) {
              $level0Submenu.css('padding-left', newValue - 70);
            }
          }
          else {
            $level0Submenu.css('padding-right', offsetRight);
          }
        }
      } else $level0Submenu.css('padding-left', 0);
    },
    bindEvents: function () {

      var body = $('body');
      var menuBreakPoint = 1150; //px
      var menuBreakPointPro = 1150; //px

      $(document)
        .on('mouseenter.menuItemLevel0', '.level0.level-top', function () {
          var $level0MenuActive = $(this);
          if (body.hasClass('cos-b2b') ) {
            menuFlyoutsPosition.setPadding($level0MenuActive, menuBreakPointPro);
          }
          if (body.hasClass('cos-b2c')) {
            menuFlyoutsPosition.setPadding($level0MenuActive, menuBreakPoint);
          }
        });
    },
    init: function () {
      this.bindEvents();
    }
  });
  return new MenuFlyoutsPositionView();
});
