define([
    'jquery',
    'backbone'
], function ($, Backbone) {

    "use strict";
    var HeaderHoverThumbnailView = Backbone.View.extend({
        initialize: function () {
        },
        bindEvents: function () {
            var $level0Menu,
                $categoryThumbnail;           
            var menuBreakPoint = $('body').hasClass('cos-b2c') ? 1150 : 980;
            
            $(document)
                .on('mouseenter.submenuImage', '.level1', function () {
                    var $that = $(this),
                        $hoverImg;
                    if (window.innerWidth > menuBreakPoint) {
                        $level0Menu = $that.parents('.level0');
                        $categoryThumbnail = $level0Menu.find('.category-thumbnail .thumbnail-image01 img');
                        if (!$categoryThumbnail.attr('default-src')) {
                            $categoryThumbnail.attr('default-src', $categoryThumbnail.attr('src'));
                        }
                        $hoverImg = $that.find('.hover-thumbnail img');
                        if ($hoverImg.length) {
                            $categoryThumbnail.attr('src', $hoverImg.attr('src'));
                        } else {
                            $categoryThumbnail.attr('src', $categoryThumbnail.attr('default-src'));
                        }
                    }
                })
                .on('mouseleave.submenuImage', '.level1', function () {
                    if (window.innerWidth > menuBreakPoint) {
                        $categoryThumbnail.attr('src', $categoryThumbnail.attr('default-src'));
                    }
                });
        },
        init: function () {
            this.bindEvents();
        }
    });
    return new HeaderHoverThumbnailView();
});
