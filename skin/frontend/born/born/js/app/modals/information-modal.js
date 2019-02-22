define([
  'jquery'
], function($) {

  var body = $('body');
  var overlay = $('.overlay');

  return function informationModal(element) {
    var $modal = element.Modal.modal;

    var modalContent = $(element.target).siblings('.information-modal-content').remove();
    $modal.append(modalContent.show());


    var openedModals = $('.modal:not(.information-modal):visible');

   /* var openedModal = _.find(element.Modal.getAllModals()['quickview-php'], function(el) {
      return el.isOpened = true;
    });*/

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
