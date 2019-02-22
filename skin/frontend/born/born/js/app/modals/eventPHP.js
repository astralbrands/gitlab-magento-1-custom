define([
  'jquery',
  'shade',
  'spinner'
], function ($, shade, spinner) {
  'use strict';
  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (element) {

    var overlay = $('.overlay');
    var url = element.currentTarget.href;
    var Modal = element.Modal;

    Modal.modal.find('.close-btn:first').on('click', Modal.close.bind(Modal));
    Modal.modal.css('display', 'inline-block');
    Modal.modal.append(spinner);
    overlay.append(Modal.modal);

    $.get(url, function (data) {})
      .done(function (data) {
        Modal.modal.find('.sk-folding-cube').hide();
        Modal.modal.find('.quickveiw-modal-inner').append($(data));

        $(element.currentTarget).trigger({
          type: 'mcallback',
          Modal: this
        });
      })
      .fail(function () {
      });
  };
  });
