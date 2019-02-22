define([
  'jquery'
], function ($) {
  'use strict';

  return function (element) {

    var overlay = $('.overlay');
    var Modal = element.Modal;
    var modalContent = $(element.target).siblings('.modal-content');
    Modal.modal.find('.quickveiw-modal-inner').append(modalContent);
    Modal.modal.find('.modal-content').show();

    Modal.open = function () {
      overlay.show();
      $('body').css('overflow', 'hidden');
      if (this.isRendered) {
        this.modal.css('display', 'inline-block');
      }
      else {
        this.render();
      }
      this.isOpened = true;
      if (Modal.modal.find('iframe').length) {
        var iframe = Modal.modal.find('iframe')[0].contentWindow;
        iframe.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
      }
    };

    Modal.close = function () {
      this.isOpened = false;
      overlay.hide();
      $('body').css('overflow', 'auto');
      if (this.modal) this.modal.hide();
      if (Modal.modal.find('iframe').length) {
        var iframe = Modal.modal.find('iframe')[0].contentWindow;
        iframe.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
      }
    };

  };
});
