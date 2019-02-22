define([
    'jquery',
    'backbone',    
    'bxslider'
], function ($, Backbone, bxSlider) {

    "use strict";

    // slider press section
    var pressObj = document.getElementById("press");
        
	if(pressObj != null){
	var pressObj_prime = pressObj.cloneNode(true);
    var PressSliderView = Backbone.View.extend({
        initialize: function () {
        },
        initPressSlider: function () {
            var tempObjCopy;
            if (window.innerWidth <= 720 && !$('.press__col-wrap').hasClass('_initialized')) {
              try {
                jQuery('.press__col-wrap').addClass('_initialized').bxSlider({
                  touchEnabled: true,
                  swipeThreshold: 100,
                  nextText: '',
                  prevText: ''
                });
              } catch (err) {
                console.log(err);
              }
            } else if (window.innerWidth > 720) {
                $('.press .bx-wrapper').remove();
                if ($('.press__col-wrap').length === 0) {
                    tempObjCopy = pressObj_prime.cloneNode(true);
                    $('.press').append(tempObjCopy);
                }
            }
        },
        bindEvents: function () {
            var pressSlider = this;
            $(window).on('resize.changePressHtml', function () {
                setTimeout(function () {
                    pressSlider.initPressSlider();
                }, 300);  // safari iphone6 plus change window width after resize event
            });
        },
        init: function () {
            this.initPressSlider();
            this.bindEvents();
        }
    });
    return new PressSliderView();
	}
});
