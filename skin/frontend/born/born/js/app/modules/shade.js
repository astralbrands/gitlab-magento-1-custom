define([
  'jquery',
  'backbone',
  'lodash',
  'mainProductSlider'
], function ($, Backbone, _, mainProductSlider) {

  "use strict";

  var $shadeList,
    $checkedTextRow,
    $shadeTop,
    $shade,
    $inputHidden,
    inputHiddenId,
    checkedData = {},
    shadeTop,
    shadeItemHtml,
    shadeView;

  var ShadeView = Backbone.View.extend({
    initialize: function () {},
    createShadeObject: function (spConfig, index, Modal) { // spConfig from pdp.js. i - index of configs

      // Added code for option swatch  which will extract correct shade attribute id when configurable product will have more that one configurable attributes.
      //if (spConfig.config.attributes[184].code !== 'shade') {
      //  for (var swatchAttrKey in spConfig.config.optionswatch_attributes) {
      //    if (spConfig.config.optionswatch_attributes[swatchAttrKey] == 'shade') {
      //      key = swatchAttrKey;
      //      break;
      //    }
      //  }
      //}
      function getAttributeId () {
              for(var key in spConfig.config.attributes){
                  if(spConfig.config.attributes[key].code == 'shade'){
                      return key;
                      break;
                  }
              }
        //return _.first(_.keys(spConfig.state));
      }

      shadeTop = '' +
        '<div class="shade-top">' +
          '<div class="shade-checked-text">Shade: ' +
            '<span class="shade-id">{{ id }}</span> ' +
            '<span class="shade-label">{{ label }}</span> ' +
            '<span class="shade-product-skus"></span>' +
          '</div>' +
        '</div>';

      $checkedTextRow = _.template(shadeTop);

      var attributeId = getAttributeId();
      var non_stockOptionIndex = _.findIndex(spConfig.config.attributes[attributeId].options, function (val, i) {
        return val.productStocks[Object.keys(val.productStocks)[0]].in_stock === true;
      });

      var non_stockFirst = spConfig.config.attributes[attributeId].options[non_stockOptionIndex];

      $shadeTop = $checkedTextRow({
        label: non_stockFirst.label,
        id: non_stockFirst.productSkus[0]
      });

      checkedData.label = spConfig.config.attributes[attributeId].options[0].label;
      checkedData.id = spConfig.config.attributes[attributeId].options[0].id;

      $shadeList = $('<ul></ul>').addClass('shade-list');

      shadeItemHtml = '' +
        '<li class="shade-item">' +
          '<a class="shade-option-item {{className}} {{in_stock}}" data-swatch="{{swatch}}" data-id="{{-id}}" data-instock="{{in_stock}}" data-label="{{-label}}" data-product-id="{{productId}}">' +
            '<img class="shade-option-item-image " src="{{-src}}">' +
          '</a>' +
        '</li>';

      var arrLength = spConfig.config.attributes[attributeId].options.length;
      for (var i = 0; arrLength > i; i++) {
        var $item = _.template(shadeItemHtml);
        var option = spConfig.config.attributes[attributeId].options[i];
        if (i == non_stockOptionIndex) {
          shadeView.checkedShadeLabel = option.label;
        }
        $shadeList.append($item({
          className: (i == non_stockOptionIndex ? 'checked' : ''),
          in_stock: option.productStocks[Object.keys(option.productStocks)[0]].in_stock ? '' : 'out-of-stock',
          label: option.label,
          productId: option.products[0],
          id: option.id,
          src: option.optionSwatch,
          swatch: option.productSkus[0]
        }));
      }

      var shadeGuideHtml = '' +
        '<span data-coherent-modal data-modal-name="shade-guide" class="shade-guide">shade guide ' +
          '<span class="icon-shadefinder">' +
            '<span class="path1"></span>' +
            '<span class="path2"></span>' +
            '<span class="path3"></span>' +
            '<span class="path4"></span>' +
            '<span class="path5"></span>' +
          '</span>' +
        '</span>' +
        '<div class="clear"></div>';

      $inputHidden = _.template('<input type="hidden" name="super_attribute[{{product}}]" id="attribute{{product}}" value="{{id}}"/>');
      inputHiddenId = 'attribute' + spConfig.config.attributes[attributeId].id;


      $shade = $('<div></div>').addClass('shade')
        .append($inputHidden({
          product: spConfig.config.attributes[attributeId].id,
          id: spConfig.config.attributes[attributeId].options[non_stockOptionIndex].id
        }))
        .append($shadeTop).append($shadeList);

      // for product group page
      if (typeof index !== 'undefined') {

        $('#product-' + spConfig.config.productId + ' .data-table-item-shade').append($($shade));

        var moreLess =
          $('<div class="btns-swatches more-less">' +
              '<a class="btn-more">More</a>' +
              '<a class="btn-less">Less</a>' +
            '</div>');

        $shade.append(moreLess).addClass('swatches-big');
        var li = $shadeList.find('li');
        if (li.length > 0 && $shadeList.width() / li.length < li.width()) {
          moreLess.show();
        }
        else {
          moreLess.hide();
        }

        //Toggle swathes opening
        moreLess.on('click', $shade, function (e) {
          e.data.toggleClass('swatches-open');
          e.stopPropagation();
        });

        if ($("#shade-guide-url").length) {
          $shade.append(shadeGuideHtml);
        }

      }
      // for product detail page
      else {
        // for quickview modal
        if (Modal) {
          $shade.insertAfter(Modal.modal.find('.product-shop .product-name'));
          if (Modal.modal.find("#shade-guide-url").length) {
            $shadeList.append(shadeGuideHtml);
          }
        }
        else {
          if ($("#shade-guide-url").length) {
            $shadeList.append(shadeGuideHtml);
          }
          $shade.insertAfter('.product-shop .product-name');
        }
      }

      $shade.append($('<p class="out-of-stock-message">Product is out of stock</p>'));

      $shade.append('<span class="ampreorder_note"></span>');

      this.bindEvents($shadeList, $shade, Modal);
      //$('#product-options-wrapper').remove();

      for(var spAttr in spConfig.config.attributes){
          $('#product-options-wrapper select[name="super_attribute['+spConfig.config.attributes[spAttr].id+']"]').remove();
      }
    },

    bindEvents: function ($shadeList, $shade, Modal) {
      function getAttributeId () {
        return _.first(_.keys(spConfig.state));
      }

      $shadeList
        .on('click', 'a', {Modal: Modal}, function (e) {
          var Modal = e.data.Modal;
          e.preventDefault();
          var $that = $(this);
          var optionsBottom =  $shade.siblings('.product-options-bottom');
          var addToCart = optionsBottom.find('.add-to-cart');
          var notifyMeBlock = $shade.siblings('.configurable-alert-block');
          var itemBottom = $shade.parent().siblings('.data-table-item-bottom'); //for pgp
          var notifyInput = $('input[name="product_id"]');
          var productId = $that.data('product-id');
          notifyInput.val(productId);
          if ($that.data('instock') === 'out-of-stock') {
            $shade.find('.out-of-stock-message').show();
            notifyMeBlock.show();
            addToCart.hide();
            itemBottom.hide();
          }
          else {
            $('.out-of-stock-message').hide();
            notifyMeBlock.hide();
            addToCart.show();
            itemBottom.show();
          }

          $shadeList.find('.checked').removeClass('checked');
          $that.addClass('checked');
          shadeView.checkedShadeLabel = $that.data('label');
          shadeView.checkedShadeId = $that.data('swatch');

          if (Modal && Modal.modal.hasClass('slider-for-shade')) {
            Modal.reloadQuickviewSlider();
          }
          else if (!Modal && $('body').hasClass('slider-for-shade')) {
            var shadeOptions = spConfig.config.attributes[getAttributeId()].options;
            
            //mainProductSlider.reloadSlider(shadeView.checkedShadeLabel, shadeOptions);
          }
         // console.log(shadeOptions);
          //jQuery.each(shadeOptions, function(key,value){
          //    console.log(key);
          //    console.log(value.mediaImage);
          //    
         /// })
          $shade.find('.shade-label').text($that.data('label'));
          $shade.find('.shade-id').text($that.data('swatch'));
          $shade.find('#' + inputHiddenId).val($that.data('id'));
          if (typeof preorderState != 'undefined') {
            //if (typeof $('#product_addtocart_form .availability')[0] !== 'undefined') {
            if (preorderState.preorderMap[$that.data('id')]) {
              $shade.find('.ampreorder_note').text(preorderState.messageMap[$that.data('id')]);
              $('.btn-cart span span').text(preorderState.cartLabel);
            }
            else {
              $shade.find('.ampreorder_note').text('');
              $('.btn-cart span span').text('Add to Bag');
            }
            //}
          }
        })
        .find('a').hover( function (e) {
          var $that = $(this);
          $shade.find('.shade-label').text($that.data('label'));
          $shade.find('.shade-id').text($that.data('swatch'));
        }, function () {
          $shade.find('.shade-label').text(shadeView.checkedShadeLabel);
          $shade.find('.shade-id').text(shadeView.checkedShadeId);
        }
      );

      $(window).on('resize.shadeList', $shade, function (e) {
        var shadeList = e.data.find('.shade-list');
        var swatchesBtns = e.data.find('.more-less');
        var li = e.data.find('li');
        if (li.length > 0 && shadeList.width() / li.length < li.width()) {
          swatchesBtns.show();
        }
        else {
          swatchesBtns.hide();
        }
      })

    },
    checkedShadeLabel: null,
    init: function (spConfig, i, Modal) {
      shadeView = this;
      this.createShadeObject(spConfig, i, Modal);
    }
  });
  return new ShadeView();
});