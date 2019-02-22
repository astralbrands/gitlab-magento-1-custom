define([
  'jquery',
  'lodash',
  'shade',
  'spinner'
], function ($, _, shade, spinner) {
  'use strict';
  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  // execute BEFORE append modal into DOM

  return function (element) {

    var overlay = $('.overlay');
    var url = element.currentTarget.pathname;
    var Modal = element.Modal;

    Modal.modal.find('.close-btn:first').on('click', Modal.close.bind(Modal));
    Modal.modal.css('display', 'inline-block');
    Modal.modal.append(spinner);
    overlay.append(Modal.modal);

     $.get(url, function (data) {})
       .done(function (data) {
         Modal.modal.find('.sk-folding-cube').hide();
         Modal.modal.find('.quickveiw-modal-inner').append($(data));
         jQuery(Modal.modal).find('select').styler();
         //Modal.modal.find('#bx-pager').attr('id', 'bx-pager-' + Modal.id);
         initSlider(element);

       })
       .fail(function () {
       });
  };

  // execute AFTER append modal into DOM

  function initSlider(element) {

    var pressSlider;
    var isMobileSliderInit = false;
    var isDesktopSliderInit = false;

    function initThumbnailsSlider() {
      try {
        pressSlider = jQuery(element.Modal.modal[0]).find('.press-modal-slider').bxSlider({
          pagerType: 'short',
          swipeThreshold: 100
        });
        isDesktopSliderInit = true;
      } catch (err) {
        console.log(err);
      }
    }

    function initDefaultSlider() {
      try {
        pressSlider = jQuery(element.Modal.modal[0]).find('.press-modal-slider').bxSlider({
          pagerType: 'short',
          swipeThreshold: 100,
        });
        isMobileSliderInit = true;
      } catch (err) {
        console.log(err);
      }
    }

    function bindEvents() {
      $(window)
        .on('resize', this, function (e) {
          if (isMobileSliderInit && window.innerWidth > 640) {
            pressSlider.destroySlider();
            initThumbnailsSlider();
          }
          if (isDesktopSliderInit && window.innerWidth <= 640) {
            pressSlider.destroySlider();
            initDefaultSlider();
          }
        });
    }

    window.innerWidth > 640 ? initThumbnailsSlider() : initDefaultSlider();
    bindEvents();
  }

});
