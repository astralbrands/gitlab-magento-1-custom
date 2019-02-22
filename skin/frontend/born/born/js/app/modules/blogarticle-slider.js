define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var blogArticleSlider;
  var storySlider;
  var isSlidersInit = false;

  var BlogArticleSliderView = Backbone.View.extend({

    initialize: function () {
    },
    slider: function (width) {

      if (width > 768 && isSlidersInit) {
        $('ul.first').addClass('products-grid');
        storySlider.destroySlider();
        if ($('.single .products-grid').length) {
          blogArticleSlider.destroySlider();
        }
        isSlidersInit = false;
      }

      if (width <= 768 && !isSlidersInit) {
        $('.single .products-grid').removeClass('products-grid').addClass('grid-slider');
        try {
          blogArticleSlider = jQuery('ul.first').bxSlider({
            slideSelector: 'li.item',
            swipeThreshold: 100
          });
          storySlider = jQuery('.related-posts').bxSlider({
            pager: false,
            slideSelector: 'li',
            infiniteLoop: true,
            swipeThreshold: 100
          });
          if ($('.single .products-grid').length) {
            if (storySlider.length && blogArticleSlider.length) {
              isSlidersInit = true;
            }
          } else {
            if (storySlider.length) {
              isSlidersInit = true;
            }
          }
        } catch (err) {
          console.log(err);
        }
      }

    },
    init: function () {
      var blogArticleSlider = this;
      blogArticleSlider.slider(window.innerWidth);

      $(window).on('resize', function () {
        blogArticleSlider.slider(window.innerWidth);
      });

    }
  });
  return new BlogArticleSliderView();
});
