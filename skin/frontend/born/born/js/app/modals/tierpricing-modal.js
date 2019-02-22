define([
  'jquery'
], function ($) {
  'use strict';

  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (guideBlock, element) {
    var Modal = element.Modal;
    Modal.modal.find('.tierpricing-modal-inner').append(guideBlock);

    var openedModals = $('.modal:not(.tierpricing-modal):visible');

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
