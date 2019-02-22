define([
    'jquery',
    'lodash',
    'backbone',
    'bxslider',
    'categorySlider'
], function($, _, Backbone, bxslider, categorySlider) {

    "use strict";
    var CategoryView = Backbone.View.extend({
        initialize: function () {
            categorySlider.init();
        }
    });
    return new CategoryView();
});
