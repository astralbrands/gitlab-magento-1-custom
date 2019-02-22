define([
  'jquery',
  'lodash',
  'backbone'
], function($, _, Backbone) {

  "use strict";
  var FooterLinksView = Backbone.View.extend({
    initialize: function() {
      var radios = $('.field-radio[data-target-id]');
      var radiosSingle =  $('.field-radio:not([data-target-id])');
      var checkBox = $('.field-expertise[data-target-id]');

      radios.on('click', function () {
        [].forEach.call($(this).closest('ul').find('.field-radio[data-target-id]'), function(item) {
          var id = $(item).data('target-id');
          $('#' + id).removeClass('show-radio-block');
        });
        var id = $(this).data('target-id');
        $('#' + id).addClass('show-radio-block');
      });
      radiosSingle.on('click', function () {
        [].forEach.call($(this).closest('ul').find('.field-radio[data-target-id]'), function(item) {
          var id = $(item).data('target-id');
          $('#' + id).removeClass('show-radio-block');
        });
      });
      checkBox.on('click', function () {
        var id = $(this).data('target-id');
        var el = $('#' + id);
        $(this).find('input').prop('checked') ? el.addClass('show-radio-block') : el.removeClass('show-radio-block');
      });
    }
  });
  return new FooterLinksView();
});
