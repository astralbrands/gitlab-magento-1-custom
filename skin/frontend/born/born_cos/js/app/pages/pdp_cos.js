define([
  'jquery',
  'lodash',
  'backbone',
  'routineSlider_cos',
  'tabsToAccordion',
  'mainProductSlider',
  'tierpricingModal'
], function ($, _, Backbone, routineSlider_cos, tabsToAccordion, mainProductSlider, tierpricingModal) {

  var PdpView = Backbone.View.extend({
    initialize: function () {

      $('.how-to-card__eye').on('click', function () {
        $(this).parents('.how-to-card').addClass('_show-back');
      });
      $('.how-to-card__close-eye').on('click', function () {
        $(this).parents('.how-to-card').removeClass('_show-back');
      });

      // routines slider
      //routineSlider.init();

      // tabs/accordion
      try {
        jQuery('.tabs-to-accordion').tabsToAccordion({
          addClassForLastTab: false
        });

        // tabs/accordion
        jQuery('.tabs-to-accordion-bazaar').tabsToAccordion({
          addClassForLastTab: false
        });
      } catch (err) {
        console.log(err);
      }
      // youtube starter
      $(".yt-video-starter").on('click', function () {
        var vi = $(".yt-video").show();
        $(this).hide();
        $('.yt-video-bg').hide();
        vi.attr("src", vi.data("autoplay-src"));
      });

      var guideBlock = $('.guide-modal').remove();
      guideBlock.css('display', 'block');
      $('[data-modal-name = "tierpricing-modal"]')
        .off('mcallback')
        .on('mcallback', tierpricingModal.bind($(this), guideBlock));

      $('.super-attribute-select').on('change', function () {
        var selectedSizeLabel = $(this).find(":selected").text();
        mainProductSlider.reloadSlider(selectedSizeLabel);

        if ($('body').hasClass('cos-b2b')) {
          updatePrice();
        }
      });

      $('.qty-input').on('input change', function () {
        if ($('body').hasClass('cos-b2b')) {
          updatePrice();
        }
      });

      function updatePrice () {
        try {
          var selectedSizeLabel = $('.super-attribute-select').find(":selected").text();
          var qty = $('.qty-input').val();
          var priceEl = $('.form-add-to-cart span.price');
          var optionsArr = spConfig.config.attributes[183].options;
          var optionIndex = _.findIndex(optionsArr, function (o) {
            return o.label == selectedSizeLabel;
          });
          var tierPriceArr = optionsArr[optionIndex].tierPrice;
          var priceIndex = _.findLastKey(tierPriceArr, function (o) {
            return parseInt(qty) >= parseInt(o.price_qty);
          });
          var price = tierPriceArr[priceIndex].price;
          priceEl.text('$' + price);
        } catch (err) {

        }
      }


      // routines slider
      routineSlider_cos.init();
      mainProductSlider.init();
    }
  });
  return new PdpView();
});
