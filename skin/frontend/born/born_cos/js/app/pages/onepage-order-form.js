define([
  'jquery',
  'lodash',
  'backbone',
  'proformManualModal'
], function ($, _, Backbone, proformManualModal) {

  var OnePageOrderView = Backbone.View.extend({
    initialize: function () {

      var qtyInInput;

      $('.qty-promotion')
        .on('focus', function () {
          qtyInInput = $(this).val();
          $(this).val('');
        })
        .on('focusout', function () {
          if (!$(this).val())  $(this).val(qtyInInput);
        });

      var manualBlock = $('#how-to-use-block-contents').remove();
      manualBlock.css('display', 'block');
      $('[data-modal-name = "proform-manual-modal"]')
        .off('mcallback')
        .on('mcallback', proformManualModal.bind($(this), manualBlock));

    }
  });
  return new OnePageOrderView();
});
