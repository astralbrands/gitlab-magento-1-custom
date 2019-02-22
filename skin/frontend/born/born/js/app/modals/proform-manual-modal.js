define([
  'jquery'
], function ($) {
  'use strict';

  // we have access to current Modal from element.Modal
  // we have access to current linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (manualBlock, element) {
    var Modal = element.Modal;
    Modal.modal.find('.proform-modal-inner').append(manualBlock);

    var openedModals = $('.modal:not(.proform-modal):visible');
    if (openedModals.length > 0) {
      openedModals.hide();

      Modal.modal.find('.close-btn:first').on('click', function() {
        $('.overlay').show();
        $('body').css('overflow', 'hidden');
        openedModals.css('display', 'inline-block');
      });
    }
  };
});

