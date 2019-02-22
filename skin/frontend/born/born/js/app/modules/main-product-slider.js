define([
  'jquery',
  'backbone',
  'bxslider'
], function ($, Backbone, bxSlider) {
  "use strict";
  var self,
    productSlider,
    isMobileSliderInit = false,
    isDesktopSliderInit = false;
  var MainProductSlider = Backbone.View.extend({
    initialize: function () {
    },
    isDomElAvailable: function () {
      var el = jQuery('.product-image-gallery-images-slider');
      var result = el.length !== 0 && typeof el.bxSlider === 'function';
      if (!result) {
        console.info("/app/design/frontend/born/born/template/catalog/product/view/media.phtml " +
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
        //  if (isMobileSliderInit && window.innerWidth > 880) {
        //    productSlider.destroySlider();
        //    self.initThumbnailsSlider();
        //  }
        //  if (isDesktopSliderInit && window.innerWidth <= 880) {
        //    productSlider.destroySlider();
        //    self.initDefaultSlider();
        //  }
        });
    },
    reloadSlider: function (checkedShadeLabel, shadeOptions) {
      //productSlider.destroySlider();
      //self.init(checkedShadeLabel, shadeOptions);
    },
    init: function (checkedShadeLabel, shadeOptions) {
      self = this;

      function findShadeObj(array, key) {
        return _.find(array, function (elem) {
          return elem.label == key;
        })
      }

      if ($('body').hasClass('slider-for-shade')) {
        var videoContainer = $('.product-image-gallery-images-slider').find('.youtube-link').clone();
        var shadeObj = findShadeObj(shadeOptions, checkedShadeLabel);
        var imagesObj = shadeObj.mediaImage;
        var newGallerySlider = $('<ul></ul>').addClass('product-image-gallery-images-slider');
        var liForVideo = $('<li></li>').addClass('product-image-gallery-images-img');
        liForVideo.append(videoContainer);
        var imageItem =
          '<li class="product-image-gallery-images-img" data-label="{{label}}">' +
          '<img class="product-image-display-video" src="{{imageSrc}}">'+
          '</li>';
        var newBxPager = $('<div></div>').addClass('product-image-gallery-thumbs').attr('id', 'bx-pager');
        var pagerItem =
          '<a data-slide-index="{{index}}" href=""  data-label="{{label}}">' +
          '<img class="product-image-display-thumbs" src="{{imageSrc}}">' +
          '</a>';

        for (var key in imagesObj) {
          if(!imagesObj.hasOwnProperty(key)) continue;
          var $imageItem = _.template(imageItem);
          var $pagerItem = _.template(pagerItem);
          if (key !== 'video') {
            newBxPager.append($pagerItem({
              index: key,
              label: checkedShadeLabel,
              imageSrc: imagesObj[key]
            }));
            newGallerySlider.append($imageItem({
              label: checkedShadeLabel,
              imageSrc: imagesObj[key]
            }));
          } else if (shadeObj.video){
            newGallerySlider.append(liForVideo);
            newBxPager.append($pagerItem({
              index: key,
              label: '',
              imageSrc: imagesObj[key]
            }));
          }
        }

        $('.product-image-gallery-images-slider').replaceWith(newGallerySlider);
        $('#bx-pager').replaceWith(newBxPager);

        for (var j = 0; j < $('#bx-pager').find('a').length; j++) {
          $($('#bx-pager').find('a')[j]).attr('data-slide-index', j);
        }
      }

    //  $(function() {
    //    window.innerWidth > 880 ? self.initThumbnailsSlider() : self.initDefaultSlider();
    //    self.bindEvents();
    //  });
    }
  });
  return new MainProductSlider();
});
