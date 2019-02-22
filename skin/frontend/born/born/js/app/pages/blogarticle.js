define([
  'jquery',
  'lodash',
  'backbone',
  'bxslider',
  'blogArticleSlider'
], function($, _, Backbone, bxslider, blogArticleSlider) {

  "use strict";

  var BlogArticleView = Backbone.View.extend({
    initialize: function () {

      blogArticleSlider.init();

      //Select styler
      if(jQuery('select').styler) {
        jQuery('select').styler();
      }

    }
  });

  return new BlogArticleView();
});
