define([
  'jquery',
  'spinner'
], function ($, spinner) {

  "use strict";

  $(function () {

    $('.link-wishlist').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      function showSpinner () {
        $(innerSpinner).show();
        $(overlay).show();
      }

      function hideSpinner () {
        $(innerSpinner).hide();
        $(overlay).hide();
      }

      var container =  $(this).closest('.box-inner')[0] || $(this).closest('.item-inner')[0] || $(this).closest('.product-slot-inner')[0];
      var overlay = $(container).find('.inner-overlay')[0];
      var innerSpinner = $(container).find('.sk-folding-cube')[0];

      if (!innerSpinner) {
        overlay = $('<div class="inner-overlay"></div>');
        var jSpinner = $(spinner).addClass('wishlist-spinner');
        $(container).append(jSpinner);
        innerSpinner = $(container).find('.sk-folding-cube')[0];
        overlay.appendTo(container);
      }
      showSpinner();

      var that = this;
      var baseUrl = BASE_URL+'bornajax/wishlist_wishlist/ajax?';

      var hrefVal = $(this).attr('href');
      var tempVar = hrefVal.substring(hrefVal.lastIndexOf('product/') + 8);
      var productId = tempVar.substring(0, tempVar.indexOf('/'));
      var paramsObj = {
        form_key: $(this).data('formkey'),
        product: productId,
        qty:1
      };

      var params = $.param(paramsObj);
      var requestUrl = baseUrl + params;

      if ($(this).hasClass('favorite')) {
        $.get(requestUrl + '&remove=1')
          .done(function (done) {
            hideSpinner();
            $(that).removeClass('favorite');
          })
          .fail(failFunc);
      }
      else {
        $.get(requestUrl)
          .done(function (done) {
            hideSpinner();
            $(that).addClass('favorite');
          })
          .fail(failFunc);
      }

      function failFunc(err) {
        console.log(err);
        window.location.href.indexOf('/pro/') > -1 ? window.location.href = "/pro/customer/account/login/" : window.location.href = "/customer/account/login/";
      }

    });

  });

});
