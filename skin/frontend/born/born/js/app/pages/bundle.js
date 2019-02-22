define([
  'jquery',
  'lodash',
  'backbone',
  'jqueryUI',
  'tabsToAccordion',
  'routineSlider',
  'mainProductSlider',
  'bundleShade',
  'modal',
  'shadeguideModal'
], function ($, _, Backbone, jqueryUI, tabsToAccordion, routineSlider, mainProductSlider, bundleShade, modal, shadeguideModal) {

  var BundleView = Backbone.View.extend({
    initialize: function () {
      var bundleInner = $('.bundle-list-inner');
      var bundleWrapper = $('.bundle-list-wrapper');

      if (typeof bundle !== 'undefined') {
        var bundleItemHtml = '' +
          '<div class="bundle-item clearfix">' +
            '<img src="{{-imgUrl}}" class="img" id="bundleMainImgUrl">' +
            '<div class="right-block" id="right-block">' +
              '<p class="title">{{-title}}</p>' +
            ' </div>' +
            '<input type="hidden" name="bundle_option[{{-optionId}}]" id="bundle-option-{{-optionId}}" value="{{-selectionId}}" />'+
          '</div>';

        var shadeGuideHtml = '' +
        '<span data-coherent-modal data-modal-name="shade-guide" class="shade-guide">shade guide ' +
          '<span class="icon-shadefinder">' +
          '<span class="path1"></span>' +
          '<span class="path2"></span>' +
          '<span class="path3"></span>' +
          '<span class="path4"></span>' +
          '<span class="path5"></span>' +
          '</span>' +
        '</span>'+
        '<div class="clear"></div>';

        bundleWrapper.prepend(shadeGuideHtml);

        modal();

        $('[data-modal-name = "shade-guide"]')
          .off('mcallback')
          .on('mcallback', shadeguideModal.bind($(this)));

        // each product on bundle page
        $.each(bundle.config.options, function (index, value) {
          var imgUrl = value.selections[Object.keys(value.selections)[0]].productSmallImage || '';

          var itemTemplate = _.template(bundleItemHtml);

          //
          var defaultSelectionId = (typeof bundle.config.selected[index] != 'undefined') ? bundle.config.selected[index][0]: '';

          var compiledItemTemplate = $(itemTemplate({'title': value.title, 'imgUrl': imgUrl, 'optionId':index, 'selectionId':defaultSelectionId}));
          bundleInner.append(compiledItemTemplate);

          // shade
          if (Object.keys(value.selections).length > 0) {
            // if we have any shade
            bundleShade.init(value.selections, compiledItemTemplate, index);
          }

        });


        bundle = undefined;

      }
    },
    isInitialize: false
  });
  return new BundleView();
});
