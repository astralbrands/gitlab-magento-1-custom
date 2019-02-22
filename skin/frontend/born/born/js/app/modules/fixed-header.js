define([
    'jquery',
    'backbone'
], function ($, Backbone) {

    "use strict";
    var FixedHeaderView = Backbone.View.extend({
        initialize: function () {
        },
        bindEvents: function () {
            var isHeaderFixed = false,
                $html = $('html');
            $(window)
                .on('load.setFixedHeader scroll.setFixedHeader', function () {
                    if ($(this).scrollTop() > 0) {
                        if (!isHeaderFixed) {
                            $html.addClass('_fixed-header');
                            isHeaderFixed = true;
                        }
                    } else {
                        if (isHeaderFixed) {
                            $html.removeClass('_fixed-header');
                            isHeaderFixed = false;
                        }
                    }
                });
        },
        init: function () {
            this.bindEvents();
        }
    });
    return new FixedHeaderView();
});
