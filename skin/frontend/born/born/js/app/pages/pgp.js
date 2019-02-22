define([
  'jquery',
  'lodash',
  'backbone',
  'shadeguideModal',
  'jqueryUI',
  'modal',
  'tabsToAccordion',
  'routineSlider',
  'mainProductSlider',
  'shade'
], function ($, _, Backbone, shadeguideModal, jqueryUI, modal, tabsToAccordion, routineSlider, mainProductSlider, shade) {

  var PgpView = Backbone.View.extend({
    initialize: function () {
      if (typeof spConfig !== 'undefined') {
        var length = spConfig.length;
        for (var i = 0; length > i; i++) {
            shade.init(spConfig[i], i);
        }
        modal();
        $('[data-modal-name = "shade-guide"]')
          .off('mcallback')
          .on('mcallback', shadeguideModal.bind($(this)));
      }
      this.isInitialize = true;
    },
    isInitialize: false
  });
  return new PgpView();
});
