define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var teamSlider;
  var isSliderInit = false;

  var TeamSliderView = Backbone.View.extend({
    initialize: function () {
    },
    initTeamSlider: function () {

      if (window.innerWidth <= 720 && !isSliderInit) {
        try {
          teamSlider = jQuery('.team .press__col-wrap').bxSlider({
            touchEnabled: true,
            swipeThreshold: 100,
            slideSelector: 'div.press__col',
            pager: false,
            nextText: '',
            prevText: ''
          });
          isSliderInit = true;
        } catch (err) {
          console.log(err);
        }
      } else if (window.innerWidth > 720 && isSliderInit) {
        teamSlider.destroySlider();
        isSliderInit = false;
      }
    },
    bindEvents: function () {
      var teamSlider = this;
      $(window).on('resize.checkTeamSlider', function () {
        $('.team .press__col-wrap').length && teamSlider.initTeamSlider();
      });
    },
    init: function () {
      $('.team .press__col-wrap').length && this.initTeamSlider();
      this.bindEvents();
    }
  });
  return new TeamSliderView();
});
