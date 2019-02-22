define([
    'jquery',
    'lodash',
    'backbone'
], function($, _, Backbone) {

    "use strict";
    var LoginView = Backbone.View.extend({
        initialize: function() {
            $('.tab').on('click', function (e){
                e.preventDefault();
                $(this).addClass('tab-active').siblings().removeClass('tab-active');
                if ($(this).hasClass('tab-professionals')){
                    $('.account-content').addClass('professionals-view');
                } else {
                    $('.account-content').removeClass('professionals-view');
                }
            });

            setTimeout(function () {    // don't remove need on login via socials page
                $(document.querySelectorAll('input:-webkit-autofill')).parents('.input-box').addClass('_filled');
            }, 500);
        }
    });
    return new LoginView();
});
