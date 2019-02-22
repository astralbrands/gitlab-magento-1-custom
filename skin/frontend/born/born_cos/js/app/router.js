define([
    'jquery',
], function ($) {

    'use strict';

    $(document).ready(function () {

        var $page = $('body');

        require(['../../born/js/app/pages/common']);
        require(['/skin/frontend/born/born_cos/js/app/pages/common_cos.js']);

        if ($page.hasClass('cms-index-index')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/home_cos.js']);
        }
        if ($page.hasClass('catalog-product-view')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/pdp_cos.js']);
            if ($page.is('[class*="-group"]')) {
                require(['../../born/js/app/pages/pgp']);
            }
        }
        if ($page.hasClass('category-landing-page')) {
            require(['../../born/js/app/pages/category']);
        }
        if ($page.hasClass('catalog-category-view') || $page.hasClass('catalogsearch-result-index')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/listing_cos.js']);
        }
        if ($page.hasClass('customer-account-login')) {
            require(['../../born/js/app/pages/login']);
        }
        if ($page.hasClass('customer-account-create')) {
            require(['../../born/js/app/pages/login']);
        }
        if ($page.hasClass('customer-account')) {
            require(['../../born/js/app/pages/account']);
        }
        if ($page.hasClass('checkout-cart-index')) {
            require(['../../born/js/app/pages/cart']);
        }
        if ($page.hasClass('cms-about-us')) {
            require(['../../born/js/app/pages/aboutus']);
            require(['/skin/frontend/born/born_cos/js/app/pages/aboutus_cos.js']);
        }
        if ($page.is('[class*="storelocator"]')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/storelocator_cos.js']);
        }
        if ($page.hasClass('catalogsearch-result-index')) {
            require(['../../born/js/app/pages/searchresults']);
        }
        if ($page.hasClass('blog')) {
            require(['../../born/js/app/pages/blog']);
        }
        else if ($page.hasClass('post-type-archive-press')) {
            require(['../../born/js/app/pages/press']);
        }
        else if ($page.hasClass('single')) {
            require(['../../born/js/app/pages/blogarticle']);
        }
        if ($page.hasClass('cms-pro-artist-program')) {
            require(['../../born/js/app/pages/footerlinks']);
        }
        if ($page.hasClass('cms-one-page')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/onepage-order-form.js']);
        }
        if ($page.hasClass('cms-professional')) {
            require(['/skin/frontend/born/born_cos/js/app/pages/footerlinks_cos.js']);
        }

    });

});