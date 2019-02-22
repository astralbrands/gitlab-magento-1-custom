define([
  'jquery',
  'lodash',
  'backbone'
], function($, _, Backbone) {

  "use strict";
  var FooterLinksView = Backbone.View.extend({
    initialize: function() {

      $('.input-field').each(function(){
        var label	 = $(this).siblings('.label-file'),
          labelVal = label.text(),
          labelRight = $(this).siblings('.label-file-right');

        $(this).on( 'change', function(e)  {
          var fileName = '';

          if(e.target.files){
            fileName = e.target.value.split( '\\' ).pop();
          }
          if(fileName) {
            label.html(fileName);
            labelRight.css('display', 'none');
          }
          else
            label.val(labelVal);
        });
      });
    }
  });
  return new FooterLinksView();
});
