define([
  'jquery',
  'lodash',
  'backbone',
  'readmoreModalPHP'
], function($, _, Backbone, readmoreModalPHP) {

  "use strict";

  var PressView = Backbone.View.extend({
    initialize: function () {

      // readmore modals
      $('[data-modal-name = "readmore-php"]')
        .on('click', function (e) {
          e.preventDefault();
        })
        .on('onParse', readmoreModalPHP.bind($(this)));

      //Show contact block
      function getUrlVars() {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++) {
          hash = hashes[i].split('=');
          vars.push(hash[0]);
          vars[hash[0]] = hash[1];
        }
        return vars;
      }
      if (!getUrlVars()['pg'] || (getUrlVars()['pg'] == 1) || (getUrlVars()['pg'] == 0) ) {
        $('.contact').css('display', 'flex');
      } else {
        $('.contact + .press__col-wrap').addClass('press__col-last');
      }

    }
  });

  return new PressView();
});

