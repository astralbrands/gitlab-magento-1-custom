define([
  'jquery',
  'modal', // modal is required for init modal windows
  'backbone',
  'qtyCounter',
  'headerHoverThumbnail_cos',
  'menuFlyoutsPosition',
  'educationModal'
], function ($, modal, Backbone, qtyCounter, headerHoverThumbnail_cos, menuFlyoutsPosition, educationModal) {

  "use strict";

  var CommonViewCos = Backbone.View.extend({
    initialize: function () {

      $('[data-modal-name = "education-modal"]')
        .off('onParse')
        .on('onParse', educationModal.bind($(this)));

      menuFlyoutsPosition.init();
      headerHoverThumbnail_cos.init();
      qtyCounter.init();

    }
  });

   return new CommonViewCos();
});
