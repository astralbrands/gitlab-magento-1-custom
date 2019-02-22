define([
  'jquery',
  'backbone',
  'lodash',
  'shade',
  'bundleShade',
  'spinner',
  'modal',
  'shadeguideModal',
  'tierpricingModal',
  'qtyCounter',
  'informationModal'
], function ($, Backbone, _, shade, bundleShade, spinner, modal, shadeguideModal, tierpricingModal, qtyCounter, informationModal) {
  'use strict';

  // we have acceess to current Modal from element.Modal
  // we have acceess to current linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM
  var productSlider;
  var isMobileSliderInit = false;
  var isDesktopSliderInit = false;
  var quickviewModalPHP;
  var el;
  var imagesSlider;
  var thumbs;

  var QuickviewModalPHP = Backbone.View.extend({
    initialize: function () {
      quickviewModalPHP = this;
    },
    quickviewPHPCallback: function (element) {
      el = element;
    },
    quickviewPHPParseTemplate: function (element) {
      el = element;
      var overlay = $('.overlay');
      var productId = element.currentTarget.getAttribute('data-modal-id');
      var Modal = element.Modal;
      Modal.reloadQuickviewSlider = quickviewModalPHP.reloadSlider;
      Modal.modal.find('.close-btn:first').on('click', Modal.close.bind(Modal));
      Modal.modal.css('display', 'inline-block');
      Modal.modal.append(spinner);
      overlay.append(Modal.modal);
	  /* BASE_URL was declared in head.phtml */
      $.get(BASE_URL+'bornajax/catalog_product/quickviewblock/id/' + productId, function (data) {
      })
        .done(function (data) {
		  if(data['status'] == 'ERROR') {
				Modal.modal.find('.quickveiw-modal-inner').append(data['message']);
				Modal.modal.find('.sk-folding-cube').hide();
				return true;
		  }
          $(element.target).parents('.btns').siblings('.product-info').find('div[id^="BVRRInlineRating"]').removeAttr('id'); //for correct bvRatings display

          Modal.modal.addClass('quickveiw-loaded');
          Modal.modal.find('.sk-folding-cube').hide();
          Modal.modal.find('.quickveiw-modal-inner').append($(data));
          try {
            jQuery(Modal.modal).find('select').styler();
          } catch (err) {
            console.log(err);
          }
          Modal.modal.find('#bx-pager').attr('id', 'bx-pager-' + Modal.id);

          Modal.galleryImagesSlider = Modal.modal.find('.product-image-gallery-images-slider');
          Modal.bxPager = Modal.modal.find('#bx-pager-' + Modal.id);
          Modal.listOfImages = Modal.galleryImagesSlider.find('li');
          Modal.listOfLinks = Modal.bxPager.find('a');

          //for COS
          thumbs = Modal.modal.find('#bx-pager-' + Modal.id);
          imagesSlider = Modal.modal.find('.product-image-gallery-images-slider');
          if (!$('body').is('[class*="cos"]')) {
            quickviewModalPHP.initShade(Modal);
          } else {
            quickviewModalPHP.initTierpricingModal(Modal);
            qtyCounter.init();
          }
          quickviewModalPHP.initInformationModal(Modal);
          Modal.modal.find('.product-image-gallery-thumbs').find('a').show();
          quickviewModalPHP.initSlider(element);
        })
        .fail(function () {
        });
    },
    initShade: function (Modal) {
      // shade
      function getAttributeId() {
        return _.first(_.keys(spConfig.state));
      }

      if (typeof spConfig !== 'undefined') {
        if (!(Array.isArray(spConfig))) {
          Modal.modal.addClass('catalog-product-view product-new-test-configurable slider-for-shade scroll-y');
          var attributeId = getAttributeId();
          var shades = spConfig.config.attributes[attributeId];
          Modal.shadeOptions = shades.options;
          if (shades) {
            shades.options.length && shade.init(spConfig, undefined, Modal);

            modal();

            //Modal.modal.find('#product-options-wrapper').remove();

            Modal.modal.find('.bundle-list-wrapper').remove();

            Modal.modal.find('[data-modal-name = "shade-guide"]')
              .off('mcallback')
              .on('mcallback', shadeguideModal.bind($(this)));
          }
          spConfig = undefined;
        }
        else {
          Modal.modal.addClass('catalog-product-view group-product product-new-test-group scroll-y');
          require(['pages/pgp'], function (pgp) {

            if (!pgp.isInitialize) {
              pgp.initialize();
            }

            //Modal.modal.find('.bundle-list-wrapper').remove();

            Modal.modal.find('[data-modal-name = "shade-guide"]')
              .off('mcallback')
              .on('mcallback', shadeguideModal.bind($(this)));

            spConfig = undefined;
            pgp.isInitialize = false;
          });
        }
      }
      else if (typeof bundle !== 'undefined') {
        Modal.modal.addClass('catalog-product-view product-bundle-view scroll-y');
        require(['pages/bundle'], function (bundleScript) {


          if (!bundleScript.isInitialize) {
            bundleScript.initialize();
          }

          Modal.modal.find('[data-modal-name = "shade-guide"]')
            .off('mcallback')
            .on('mcallback', shadeguideModal.bind($(this)));
          bundle = undefined;
          bundleScript.isInitialize = false;
        });
      }
      else { // virtual card, pdp without spConfig
        Modal.modal.addClass('scroll-y');
        Modal.modal.find('.bundle-list-wrapper').remove();
      }

    },
    initSlider: function (element, selectedSizeLabel) {

      function initThumbnailsSlider() {
        try {
          element.Modal.productSlider = jQuery(element.Modal.modal[0]).find('.product-image-gallery-images-slider').bxSlider({
            video: true,
            swipeThreshold: 100,
            mode: 'fade',
            pagerCustom: '#bx-pager-' + element.Modal.id,
            controls: false
          });
          isDesktopSliderInit = true;
        } catch (err) {
          console.log(err);
        }
      }

      function initDefaultSlider() {
        try {
          element.Modal.productSlider = jQuery(element.Modal.modal[0]).find('.product-image-gallery-images-slider').bxSlider({
            video: true,
            pager: false,
            swipeThreshold: 100,
            nextText: '',
            prevText: ''
          });
          isMobileSliderInit = true;
        } catch (err) {
          console.log(err);
        }
      }

      function bindEvents() {
        $(window).on('resize', this, function (e) {
          if (isMobileSliderInit && window.innerWidth > 880) {
            element.Modal.productSlider.destroySlider();
            initThumbnailsSlider();
          }
          if (isDesktopSliderInit && window.innerWidth <= 880) {
            element.Modal.productSlider.destroySlider();
            initDefaultSlider();
          }
        });
        if ($('body').is('[class*="cos"]')) {
          Modal.modal.find('.super-attribute-select').on('change', function () {
            var selectedSizeLabel = $(this).find(":selected").text();
            quickviewModalPHP.reloadSlider(selectedSizeLabel);

            if ($('body').hasClass('cos-b2b')) {
              updatePrice();
            }
          });

          Modal.modal.find('.qty-input').on('input change', function () {
            if ($('body').hasClass('cos-b2b')) {
              updatePrice();
            }
          });

          var updatePrice = function () {
            try {
              var selectedSizeLabel = Modal.modal.find('.super-attribute-select').find(":selected").text();
              var qty = Modal.modal.find('.qty-input').val();
              var priceEl = Modal.modal.find('.form-add-to-cart span.price');
              var optionsArr = spConfig.config.attributes[183].options;
              var optionIndex = _.findIndex(optionsArr, function (o) {
                return o.label == selectedSizeLabel;
              });
              var tierPriceArr = optionsArr[optionIndex].tierPrice;
              var priceIndex = _.findLastKey(tierPriceArr, function (o) {
                return qty >= o.price_qty;
              });
              var price = tierPriceArr[priceIndex].price;
              priceEl.text('$' + price);
            } catch (err) {

            }
          }
        }
      }

      function findShadeObj(array, key) {
        return _.find(array, function (elem) {
          return elem.label == key;
        })
      }

      var checkedShadeLabel = shade.checkedShadeLabel;
      var Modal = element.Modal;

      if (Modal.modal.hasClass('slider-for-shade')) {
        var videoContainer = Modal.modal.find('.product-image-gallery-images-slider').find('.youtube-link').clone();
        var shadeObj = findShadeObj(Modal.shadeOptions, checkedShadeLabel);
        var imagesObj = shadeObj.mediaImage;
        var newGallerySlider = $('<ul></ul>').addClass('product-image-gallery-images-slider');
        var liForVideo = $('<li></li>').addClass('product-image-gallery-images-img');
        liForVideo.append(videoContainer);
        var imageItem =
          '<li class="product-image-gallery-images-img" data-label="{{label}}">' +
          '<img src="{{imageSrc}}">' +
          '</li>';

        var newBxPager = $('<div></div>').addClass('product-image-gallery-thumbs').attr('id', 'bx-pager-' + Modal.id);
        var pagerItem =
          '<a data-slide-index="{{index}}" href=""  data-label="{{label}}">' +
          '<img src="{{imageSrc}}">' +
          '</a>';

        for (var key in imagesObj) {
          if (!imagesObj.hasOwnProperty(key)) continue;
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
          } else if (shadeObj.video) {
            newGallerySlider.append(liForVideo);
            newBxPager.append($pagerItem({
              index: key,
              label: '',
              imageSrc: imagesObj[key]
            }));
          }
        }

        Modal.modal.find('.product-image-gallery-images-slider').replaceWith(newGallerySlider);
        Modal.modal.find('#bx-pager-' + Modal.id).replaceWith(newBxPager);

        for (var j = 0; j < $('#bx-pager-' + Modal.id).find('a').length; j++) {
          $(Modal.modal.find('#bx-pager-' + Modal.id).find('a')[j]).attr('data-slide-index', j);
        }
      }
      else if ($('body').is('[class*="cos"]')) {
        var commonImagesLabel = 'media';

        var imagesSliderClone = imagesSlider.clone();
        var thumbsClone = thumbs.clone();
        var newImagesSliderLi = imagesSliderClone.find('li[data-label="' + selectedSizeLabel + '"]');
        var newThumbsA = thumbsClone.find('a[data-label="' + selectedSizeLabel + '"]');
        var defaultImages = imagesSliderClone.find('li.media');
        var defaultImagesThumbs = thumbsClone.find('a.media');
        var commonImages = imagesSliderClone.find('li[data-label="' + commonImagesLabel + '"]');
        var commonImagesThumbs = thumbsClone.find('a[data-label="' + commonImagesLabel + '"]');

        if (!selectedSizeLabel || selectedSizeLabel.toLowerCase().indexOf('choose') > -1) {
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
        $('#bx-pager-' + Modal.id).replaceWith(thumbsClone);
      }

      window.innerWidth > 880 ? initThumbnailsSlider() : initDefaultSlider();
      bindEvents();
      Modal.modal.find('.product-image-gallery-images-slider').css('opacity', 1);
      Modal.modal.find('.product-image-gallery-thumbs').css('opacity', 1);
    },
    reloadSlider: function (selectedSizeLabel) {
      if (el.Modal.productSlider.destroySlider)
        el.Modal.productSlider.destroySlider();
      quickviewModalPHP.initSlider(el, selectedSizeLabel);
    },
    initTierpricingModal: function (Modal) {
      modal();
      var guideBlock = Modal.modal.find('.guide-modal').remove();
      guideBlock.css('display', 'block');
      Modal.modal.find('[data-modal-name = "tierpricing-modal"]')
        .off('mcallback')
        .on('mcallback', tierpricingModal.bind($(this), guideBlock));
    },
    initInformationModal: function (Modal) {
      modal();
      Modal.modal.find('[data-modal-name = "information-modal"]')
        .off('mcallback')
        .on('mcallback', informationModal.bind($(this)));
    }
  });

  return new QuickviewModalPHP();
});
