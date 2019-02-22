define([
  'jquery'
], function($) {

  var body = $('body');
  var overlay = $('.overlay');

  return function ShadeGuideFunc(element) {
    var $modal = element.Modal.modal;
    if ($(element.target).parents('.filter-container').length) {
      var modalContent = $(element.target).parents('.filter-container').find('.shade-guide-contents');
      modalContent.find('img').addClass('shade-guide-img');
      $modal.append(modalContent.show());
      jQuery(modalContent).bxSlider({
        infiniteLoop: true,
        swipeThreshold: 100,
        slideSelector: 'p'
      });

    } else {
      var shadeGuideBlock = $('body').find('#shade-guide-url').clone();
      if (shadeGuideBlock.length) {
        if (!$modal.find('#shade-guide-url').length) {
          shadeGuideBlock.find('img').addClass('shade-guide-img');
          shadeGuideBlock.css('display', 'block');
          $modal.append(shadeGuideBlock);
          jQuery($modal).find('#shade-guide-url').bxSlider({
            infiniteLoop: true,
            swipeThreshold: 100,
            slideSelector: 'p'
          });
        }
      } else {
        if (!$modal.find('.shade-guide-img').length) {
          var img = $('<img class="shade-guide-img">');
          img.attr('src', '/media/wysiwyg/2015_Pur_Shade_Patching_Guide.png');
          $modal.append(img);
        }
      }
    }
   /* var src = $modal.find('img').attr('src') || '/media/wysiwyg/2015_Pur_Shade_Patching_Guide.png';
    $modal.find('img').attr('src', src);*/

    var openedModals = $('.modal:not(.shade-guide-modal):visible');

    var openedModal = _.find(element.Modal.getAllModals()['quickview-php'], function(el) {
      return el.isOpened = true;
    });

    if (openedModals.length > 0) {
      openedModals.hide();

      $modal.find('.close-btn:first').on('click', function() {
        overlay.show();
        body.css('overflow', 'hidden');
        openedModals.css('display', 'inline-block');
      });
    }
  }

});
