define([
  'jquery'
], function ($) {
  'use strict';
  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (element) {

    var overlay = $('.overlay');
    var Modal = element.Modal;
    var iframeEl = Modal.modal.find('iframe');

    var videoLink = $(element.target).data('modal-video-link');
    var positionForEmbed = videoLink.indexOf('.be/') > -1 ? videoLink.indexOf('.be/') + 4 : videoLink.indexOf('.com/') + 4;
    var videoId = videoLink.indexOf('watch?') > -1 ? videoLink.slice(positionForEmbed + 9) : videoLink.slice(positionForEmbed);
    var videoSrc = 'https://youtube.com/embed/' + videoId + '?rel=0&autoplay=1&enablejsapi=1';

    $(iframeEl).attr('src', videoSrc);

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
      var iframe = Modal.modal.find('iframe')[0].contentWindow;
      iframe.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
    };

    Modal.close = function () {
      this.isOpened = false;
      overlay.hide();
      $('body').css('overflow', 'auto');
      if (this.modal) this.modal.hide();
      var iframe = Modal.modal.find('iframe')[0].contentWindow;
      iframe.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
    };

  };
});
