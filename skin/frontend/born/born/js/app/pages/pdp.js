define([
  'jquery',
  'lodash',
  'backbone',
  'modal',
  'jqueryUI',
  'tabsToAccordion',
  'routineSlider',
  'mainProductSlider',
  'shade',
  'shadeguideModal'
], function ($, _, Backbone, modal, jqueryUI, tabsToAccordion, routineSlider, mainProductSlider, shade, shadeguideModal) {
  var PdpView = Backbone.View.extend({
    initialize: function () {
      function getAttributeId () {
        return _.first(_.keys(spConfig.state));
      }
      console.log("Pdp Initialized");
      $('.how-to-card__eye').on('click', function () {
        $(this).parents('.how-to-card').addClass('_show-back');
      });
      $('.how-to-card__close-eye').on('click', function () {
        $(this).parents('.how-to-card').removeClass('_show-back');
      });
      routineSlider.init();
      try {
        jQuery('.tabs-to-accordion').tabsToAccordion({
          addClassForLastTab: false
        });
        jQuery('.tabs-to-accordion-bazaar').tabsToAccordion({
          addClassForLastTab: false
        });
      } catch (err) {
        console.log(err);
      }
      $(".yt-video-starter").on('click', function () {
        var vi = $(".yt-video").show();
        $(this).hide();
        $('.yt-video-bg').hide();
        vi.attr("src", vi.data("autoplay-src"));
      });
      if (typeof spConfig !== 'undefined') {
        if (!(Array.isArray(spConfig))) {
          var attributeId = getAttributeId();
          $('#product-options-wrapper').find('select[name = "super_attribute[' + attributeId + ']"]').parentsUntil('dl').remove();  // todo rewrite or remove script which generate swatch select
          $('body').addClass('slider-for-shade');
          spConfig.config.attributes[attributeId].options.length && shade.init(spConfig);
          modal();
          $('[data-modal-name = "shade-guide"]')
            .off('mcallback')
            .on('mcallback', shadeguideModal.bind($(this)));
        }
      }
      if (typeof spConfig !== 'undefined') {
        if (!(Array.isArray(spConfig))) {
          var shadeOptions = spConfig.config.attributes[attributeId].options;
        }
      }
      mainProductSlider.init(shade.checkedShadeLabel, shadeOptions);
    }
  });
  return new PdpView();
});