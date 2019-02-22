define([
  'jquery'
], function($) {

  return function (element) {
    var Modal = element.Modal;
    var modalContent = $(element.target).siblings('#shipping-return').remove();
    Modal.modal.append(modalContent);
    Modal.modal.find('#shipping-return').show();
  }
});
