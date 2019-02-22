define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var self,
    productSlider,
    isMobileSliderInit = false,
    isDesktopSliderInit = false;
  var imagesSlider = $('.product-image-gallery-images-slider');
  var thumbs =  $('.product-image-gallery-thumbs');

  var MainProductSlider = Backbone.View.extend({
    initialize: function () {
    },
    isDomElAvailable: function () {
      var el = jQuery('.product-image-gallery-images-slider');
      var result = el.length !== 0 && typeof el.bxSlider === 'function';
      if (!result) {
        console.info("/app/design/frontend/rwd/born/template/catalog/product/view/media.phtml " +
          "is not return #product-image-gallery-images-slider element. Please, fix it in PHP");
      }
      return result;
    },
    initThumbnailsSlider: function (message) {
      message ? console.log(message) : '';
      var that = this;
      if (typeof productSlider !== 'undefined') {
        productSlider.destroySlider();
      }

      if (!that.isDomElAvailable()) {
        return false;
      }

      try {
        var thumbs =  $('.product-image-gallery-thumbs');
        var imagesSlider = $('.product-image-gallery-images-slider');
        thumbs.find('a').show();
        imagesSlider.find('li').show();
        productSlider = jQuery('.product-image-gallery-images-slider').bxSlider({
          video: true,
          pagerCustom: '#bx-pager',
          swipeThreshold: 100,
          mode: 'fade',
          controls: false
        });
        if (productSlider.length)
          isDesktopSliderInit = true;
        thumbs.css('opacity', 1);
        imagesSlider.css('opacity', 1);
      } catch (err) {
        console.log(err);
      }
    },
    initDefaultSlider: function (message) {
      var thumbs =  $('.product-image-gallery-thumbs');
      var imagesSlider = $('.product-image-gallery-images-slider');
      message ? console.log(message) : '';
      var that = this;
      if (typeof productSlider !== 'undefined') {
        productSlider.destroySlider();
      }

      if (!that.isDomElAvailable()) {
        return false;
      }
      try {
        thumbs.find('a').show();
        imagesSlider.find('li').show();
        thumbs.find('a').show();
        imagesSlider.find('li').show();
        productSlider = jQuery('.product-image-gallery-images-slider').bxSlider({
          video: true,
          pager: false,
          nextText: '',
          swipeThreshold: 100,
          prevText: ''
        });
        if (productSlider.length)
          isMobileSliderInit = true;
        thumbs.css('opacity', 1);
        imagesSlider.css('opacity', 1);
      } catch (err) {
        console.log(err);
      }
    },
    bindEvents: function () {
      var self = this;
      $(window)
        .on('resize.reInitMainProductSlider', this, function () {
          if (isMobileSliderInit && window.innerWidth > 880) {
            productSlider.destroySlider();
            //self.initThumbnailsSlider();
          }
          if (isDesktopSliderInit && window.innerWidth <= 880) {
            productSlider.destroySlider();
            //self.initDefaultSlider();
          }
        });
    },
    reloadSlider: function (selectedSizeLabel) {
      productSlider.destroySlider();
      self.init(selectedSizeLabel);
    },
    init: function (selectedSizeLabel) {

      var commonImagesLabel = 'media';
      self = this;
      var imagesSliderClone = imagesSlider.clone();
      var thumbsClone = thumbs.clone();
      var newImagesSliderLi = imagesSliderClone.find('li[data-label="' + selectedSizeLabel + '"]');
      var newThumbsA = thumbsClone.find('a[data-label="' + selectedSizeLabel + '"]');
      var defaultImages = imagesSliderClone.find('li.media');
      var defaultImagesThumbs = thumbsClone.find('a.media');
      var commonImages = imagesSliderClone.find('li[data-label="' + commonImagesLabel + '"]');
      var commonImagesThumbs = thumbsClone.find('a[data-label="' + commonImagesLabel + '"]');

      if (!selectedSizeLabel || selectedSizeLabel.toLowerCase().indexOf('choose') > -1){
        newImagesSliderLi = $.merge(newImagesSliderLi, defaultImages);
        newThumbsA = $.merge(newThumbsA, defaultImagesThumbs);
      }

      newImagesSliderLi = $.merge(newImagesSliderLi, commonImages);
      newThumbsA = $.merge(newThumbsA, commonImagesThumbs);

      imagesSliderClone.html(newImagesSliderLi);
      thumbsClone.html(newThumbsA);


      for (var j = 0; j < $(thumbsClone).find('a').length; j++) {
        $(thumbsClone.find('a')[j]).attr('data-slide-index', j);
      }

      $('.product-image-gallery-images-slider').replaceWith(imagesSliderClone);
      $('#bx-pager').replaceWith(thumbsClone);

      $(function() {
        //window.innerWidth > 880 ? self.initThumbnailsSlider() : self.initDefaultSlider();
        self.bindEvents();
      });
    }
  });
  return new MainProductSlider();
});
