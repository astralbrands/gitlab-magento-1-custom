define([
  'jquery',
  'backbone',
  'lodash'
], function ($, Backbone, _) {

  "use strict";

  var QtyCounterView = Backbone.View.extend({
    className: "qty-wrapper",
    events: {
      "click .qty-minus": "onMinus",
      "click .qty-plus": "onPlus"
    },
    initialize: function () {
    },
    onMinus: function () {
      var input = $(this).parent().find('.qty-input');
      var count = parseInt(input.val()) - 1;
      count = count < 1 ? 1 : count;
      input.val(count);
      input.change();
      return false;
    },
    onPlus: function () {
      var input = $(this).parent().find('input');
      input.val(parseInt(input.val()) + 1);
      input.change();
      return false;
    },
    checkNum: function (e) {
      var input = $(this).parent().find('input');
      var rep = /[^\d\.]+/g;
      var value = input.val();
      if (value < 1 || rep.test(value)) {
        input.val(1);
      }
      input.change();
      return false;
    },
    keyPress: function (e) {
      if (e.which == '13') {
        e.preventDefault();
      }
    },
    init: function () {
      var self = this;
      $('.qty-minus').off('click').on('click', self.onMinus);
      $('.qty-plus').off('click').on('click', self.onPlus);
      $('.qty-input')
        .off('input')
        .on('input', self.checkNum)
        .on('keydown', self.keyPress);
    }
  });
  return new QtyCounterView();
});

