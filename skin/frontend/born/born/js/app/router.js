define([
  'jquery',
], function ($) {

  'use strict';

  $(document).ready(function () {

    var $page = $('body');

    require(['pages/common']);

    if ($page.hasClass('cms-index-index')) {
      require(['pages/home']);
    }
    if ($page.hasClass('catalog-product-view')) {
      require(['pages/pdp']);
      if ($page.is('[class*="-group"]') || $page.hasClass('group-product')) {
        require(['pages/pgp']);
      }
      if ($page.is('[class*="-bundle"]')) {
        require(['pages/bundle']);
      }
    }
    if ($page.hasClass('category-landing-page')) {
      require(['pages/category']);
    }
    if ($page.hasClass('catalog-category-view') || $page.hasClass('catalogsearch-result-index')) {
      require(['pages/listing']);
    }
    if ($page.hasClass('customer-account-login')) {
      require(['pages/login']);
    }
    if ($page.hasClass('customer-account-create')) {
      require(['pages/login']);
    }
    if ($page.hasClass('customer-account')) {
      require(['pages/account']);
    }
    if ($page.hasClass('checkout-cart-index')) {
      require(['pages/cart']);
    }
    if ($page.hasClass('cms-about-us')) {
      require(['pages/aboutus']);
    }
    if ($page.is('[class*="storelocator"]')) {
      require(['pages/storelocator']);
    }
    if ($page.hasClass('catalogsearch-result-index')) {
      require(['pages/searchresults']);
    }
    if ($page.hasClass('blog')) {
      require(['pages/blog']);
    }
    else if ($page.hasClass('post-type-archive-press')) {
      require(['pages/press']);
    }
    else if ($page.hasClass('single')) {
      require(['pages/blogarticle']);
    }
    if ($page.hasClass('cms-pro-artist-program')) {
      require(['pages/footerlinks']);
    }
  });

});
