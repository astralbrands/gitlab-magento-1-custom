define([
  'jquery',
  'backbone',
  'lodash'
], function ($, Backbone, _) {

  'use strict';

  var shadeList = null,
    textHtml = null,
    textBlock = null,
    shadeEl = null,
    shadeItemHtml = null,
    bundleShade = {};

  var ShadeView = Backbone.View.extend({
    initialize: function () {
    },
    createShadeObject: function (options, compiled, optionId) { // spConfig from pdp.js. i - index of configs
      shadeEl = $('<div></div>').addClass('shade');

      function checkListLenght (shadeList, swathesBtns) {
        if (li.length > 0 && shadeList.width() / li.length < li.width()) {
          swathesBtns.show();
        }
        else {
          swathesBtns.hide();
        }
      }

      textHtml = '' +
        '<div class="shade-top">' +
          '<div class="shade-checked-text">Shade: ' +
            '<span class="shade-sku">{{- productSkus }}</span>' +
            '<span class="shade-label">{{- name }}</span> ' +
            //'<span class="ampreorder_note" id="ampreorder_note_shade"></span>' +
          '</div>' +
        '</div>';
      textBlock = _.template(textHtml);

      shadeItemHtml = '' +
        '<li class="shade-item">' +
          '<input type="hidden" id="productHoverImage" value="{{-productSmallImage}}">' +
          '<a class="shade-option-item {{className}} {{in_stock}}" data-swatch="{{swatch}}" data-id="{{-productId}}" data-selection="{{-selectionId}}" data-instock="{{in_stock}}" data-label="{{-label}}">' +
            '<img class="shade-option-item " src="{{-src}}">' +
          '</a>' +
        '</li>';

      shadeList = $('<ul></ul>').addClass('shade-list');

      var initialBlock = null;
      var i = 0;

      //var defaultSelectionId = (typeof bundle.config.selected[optionId] !== 'undefined') ? bundle.config.selected[optionId][0] : '';

      var defaultSelectionId = _.findKey(options, function (val, i) {
        return val.in_stock === true;
      });

      for (var selectionId in options) {
        var item = _.template(shadeItemHtml);
        if (i == 0) {
          initialBlock = options[selectionId];
        }

        if (options[selectionId].swatchImage) {
          shadeList.append(item({
            selectionId: selectionId,
            className: (defaultSelectionId == selectionId ? 'checked' : ''),
            label: options[selectionId].name,
            src: options[selectionId].swatchImage,
            swatch: options[selectionId].productSkus,
            in_stock: options[selectionId].in_stock ? '' : 'out-of-stock',
            productId: options[selectionId].product_id,
            productSmallImage: options[selectionId].productSmallImage + '?' + i, // for testing dynamic change image url
            productSkus: options[selectionId].productSkus
          }));
        }
        i++;
      }

      shadeEl
        .append(textBlock(initialBlock))
        .append(shadeList);

      compiled.find('#right-block').append(shadeEl);

      var shadeElDom = compiled.find('#right-block').find('.shade');
      var shadeListDom = shadeElDom.find('.shade-list');

      var moreLess =
        $('<div class="btns-swatches more-less">' +
            '<a class="btn-more">More</a>' +
            '<a class="btn-less">Less</a>' +
          '</div>');

      if (shadeListDom.find('li').length > 3) {
        shadeElDom.append(moreLess).addClass('swatches-big');

        var li = shadeListDom.find('li');
        var swathesBtns = shadeElDom.find('.more-less');
        setTimeout(function() {
          checkListLenght(shadeListDom, swathesBtns);
        }, 800);
        setInterval (function() {
          checkListLenght(shadeListDom, swathesBtns);
        }, 5000);


        //Toggle swathes opening
        swathesBtns.on('click', shadeElDom, function (e) {
          e.data.toggleClass('swatches-open');
          e.stopPropagation();
        });

      }

      compiled.find('#right-block').append($('<p class="out-of-stock-message">Product is out of stock</p>'));

      compiled.find('#right-block').append('<span class="ampreorder_note"></span>');

      this.bindEvents(compiled, shadeListDom, shadeElDom, optionId);

      // code for amasty preorder extension integration
      if (typeof preorderState !== 'undefined') {
        if (preorderState.options[optionId].selectionPreorderMap && preorderState.options[optionId].selectionPreorderMap[optionId]) {
          compiled.find('.ampreorder_note').text(preorderState.options[optionId].selectionMessageMap[defaultSelectionId]);
        }
        else {
          compiled.find('.ampreorder_note').text();
        }
      }
      $('#product-options-wrapper').remove();
    },

    bindEvents: function (compiled, $shadeList, $shade, optionId) {
      bundleShade.checkedShadeLabel = $shadeList.find('a.checked').data('label');
      bundleShade.checkedShadeId = $shadeList.find('a.checked').data('swatch');
      $shadeList
        .on('click', 'a', function (e) {
          e.preventDefault();
          var $that = $(this);
          if ($that.data('instock') === 'out-of-stock') {
            compiled.find('.out-of-stock-message').show();
            //return false;
          }
          else {
            $('.out-of-stock-message').hide();
          }
          $shadeList.find('.checked').removeClass('checked');
          $that.addClass('checked');

          bundleShade.checkedShadeLabel = $that.data('label');
          bundleShade.checkedShadeId = $that.data('swatch');

          compiled.find('#bundleMainImgUrl').attr('src', $that.siblings('#productHoverImage').val());
          $shade.find('.shade-label').text($that.data('label'));
          $shade.find('.shade-id').text($that.data('swatch'));
          //$shade.find('#attribute184').val($that.data('id'));
          $('#bundle-option-' + optionId).attr('value', $that.data('selection'));
          // code for amasty preorder extension integration
          if (typeof preorderState != 'undefined') {
            //if (typeof $('#ampreorder_note_' + optionId) != 'undefined') {
              if (preorderState.options[optionId].selectionPreorderMap[$that.data('selection')]) {

                compiled.find('.ampreorder_note').text(preorderState.options[optionId].selectionMessageMap[$that.data('selection')]);
                $('.btn-cart span span').text(preorderState.cartLabel);
              }
              else {
                compiled.find('.ampreorder_note').text('');
                $('.btn-cart span span').text('Add to Bag');
              }
            //}
          }
        })
        .find('a').hover(
        function (e) {
          var $that = $(this);
          compiled.find('#bundleMainImgUrl').attr('src', $that.siblings('#productHoverImage').val());
          $shade.find('.shade-label').text($that.data('label'));
          $shade.find('.shade-sku').text($that.data('swatch'));
        }, function () {
          $shade.find('.shade-label').text(bundleShade.checkedShadeLabel);
          $shade.find('.shade-sku').text(bundleShade.checkedShadeId);
        }
      );

      $(window).on('resize.shadeList', $shade, function (e) {
        var shadeList = e.data.find('.shade-list');
        var swatchesBtns = e.data.find('.more-less');
        var li = shadeList.find('li');
        if (li.length > 0 && shadeList.width() / li.length < li.width()) {
          swatchesBtns.show();
        }
        else {
          swatchesBtns.hide();
        }
      })
    },
    init: function (option, position, optionId) {
      this.createShadeObject(option, position, optionId);
    }
  });
  return new ShadeView();
});
