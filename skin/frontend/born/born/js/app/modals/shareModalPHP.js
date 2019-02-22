define([
  'jquery'
], function ($) {
  'use strict';

  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (element) {
    function addScripts(srcArray) {
      for (var i = 0; i < srcArray.length; i++) {
        var script = document.createElement('script');
        script.src = srcArray[i];
        $('head').append(script);
      }
    }

    var overlay = $('.overlay');
    var Modal = element.Modal;
    Modal.modal.find('.close-btn:first').on('click', Modal.close.bind(Modal));
    Modal.modal.find('.quickveiw-modal-inner').append('<div id="AddShoppersRefer"></div>');
    Modal.modal.css('display', 'inline-block');
    overlay.append(Modal.modal);
  };
});
