define([
  'jquery',
  'lodash'
], function ($, _) {

  // we have acceess to current Modal from element.Modal
  // we have acceess to current linkElem / linkEl from element.currentTarget

  return {
    quickviewCallback: quickviewCallback,
    quickviewParseTemplate: quickviewParseTemplate
  };

  // execute AFTER append modal into DOM
  function quickviewCallback(element) {

    var MainProductSlider = (function () {

      var productSlider;

      function initThumbnailsSlider() {
        try {
          productSlider = jQuery(element.Modal.modal[0]).find('.product-image-gallery-images-slider').bxSlider({
            pagerCustom: '#bx-pager-' + element.Modal.id,
            swipeThreshold: 100,
            controls: false
          });
        } catch (err) {
          console.log(err);
        }
      }

      return {
        init: function () {
          initThumbnailsSlider();
        }
      };

    })();
    MainProductSlider.init();
  }

  // execute BEFORE append modal into DOM
  function quickviewParseTemplate(element) {
    var modelJson = JSON.parse(element.currentTarget.getAttribute('data-modal-json'));
    var htmlString = element.Modal.modal[0].outerHTML;

    element.Modal.modal = $(_.template(htmlString, {
      'imports': {
        '$': jQuery,
        'Modal': element.Modal
      }
    })(modelJson));

    jQuery(element.Modal.modal).find('select').styler();


    //$(element.Modal.modal[0]).find('.product-image-gallery-images-slider').bxSlider();
  }

});
