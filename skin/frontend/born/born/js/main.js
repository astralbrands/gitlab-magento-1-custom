require.config({
  waitSeconds: 0,
  baseUrl: 'https://cf.astralbrandsmedia.com/skin/frontend/born/born/js/app',
  paths: {
    'lodash': '../lib/lodash.min',
    'backbone': '../lib/backbone',
    'jquery': '../lib/jquery',
    'jqueryUI': '../lib/jquery-ui.min',
    'jqueryFormstyler': '../lib/jquery.formstyler.min',
    'text': '../lib/text',
    'imagesloaded': '../lib/imagesloaded.min',
    'bxslider': '../lib/bxslider',
    'owlslider': '../../born/lib/owl/owl.carousel.min/',
    'tabsToAccordion': '../lib/jquery.tabs-to-accordion',
    'markerwithlabel': '../lib/markerwithlabel',
    'browserDetect': '../components/browser-detect',
    'modal': '../components/modal',
    'wishlist': '../components/wishlist',
    'shippingModal': '../app/modals/shipping-returns',
    'quickviewModal': '../app/modals/quickview',
    'quickviewModalPHP': '../app/modals/quickviewPHP',
    'lookModal': '../app/modals/look',
    'shadeguideModal': '../app/modals/shade-guide',
    'fixedHeader': '../app/modules/fixed-header',
    'backToTop': '../app/modules/back-to-top',
    'headerHoverThumbnail': '../app/modules/header-hover-thumbnail',
    'routineSlider': '../app/modules/routine-slider',
    'mainProductSlider': '../app/modules/main-product-slider',
    'pressSlider': '../app/modules/press-slider',
    'minicart': '../app/modules/minicart',
    'sampleSlider': '../app/modules/sample-slider',
    'categorySlider': '../app/modules/category-slider',
    'shade': '../app/modules/shade',
    'bundleShade': '../app/modules/bundle-shade',
    'bestsellersSlider': '../app/modules/bestsellers-slider',
    'blogSlider': '../app/modules/blog-slider',
    'blogEvents': '../app/modules/blog-events',
    'blogArticleSlider': '../app/modules/blogarticle-slider',
    'spinner': '../components/spinner',
    'shareModalPHP': '../app/modals/shareModalPHP',
    'readmoreModalPHP': '../app/modals/readmorePHP',
    'eventModalPHP': '../app/modals/eventPHP',
    'infobox': '../lib/infobox',
    'pgp': '../pages/pgp',
    'bundle': '../pages/bundle',
    'undertoneModal': '../app/modals/undertone-guide',
    'videoInImage': '../app/modules/video-in-image',
    'tierpricingModal': '../app/modals/tierpricing-modal',
    'proformManualModal': '../app/modals/proform-manual-modal',
    'informationModal': '../app/modals/information-modal',
    'qtyCounter': '../app/modules/qty-counter'
  },
  shim: {
    lodash: {
      exports: '_'
    },
    backbone: {
      deps: ['jquery', 'lodash'],
      exports: 'Backbone'
    },
    bxslider: {
      deps: ['jquery'],
      exports: 'bxslider'
    },
    owlslider: {
      deps: ['jquery'],
      exports: 'owlslider'
    },
    tabsToAccordion: {
      deps: ['jquery'],
      exports: 'tabsToAccordion'
    },
    jqueryUI: {
      deps: ['jquery'],
      exports: 'jqueryUI'
    },
    jqueryFormstyler: {
      deps: ['jquery'],
      exports: 'styler'
    },
    fixedHeader: {
      deps: ['jquery'],
      exports: 'fixedHeader'
    },
    backToTop: {
      deps: ['jquery'],
      exports: 'backToTop'
    },
    headerHoverThumbnail: {
      deps: ['jquery'],
      exports: 'headerHoverThumbnail'
    },
    routineSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'routineSlider'
    },
    mainProductSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'mainProductSlider'
    },
    pressSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'pressSlider'
    },
    blogSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'blogSlider'
    },
    blogEvents: {
      deps: ['jquery'],
      exports: 'blogEvents'
    },
    blogArticleSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'blogArticleSlider'
    },
    sampleSlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'sampleSlider'
    },
    minicart: {
      deps: ['jquery'],
      exports: 'minicart'
    },
    categorySlider: {
      deps: ['jquery', 'bxslider'],
      exports: 'categorySlider'
    },
    shade: {
      deps: ['jquery'],
      exports: 'shade'
    }
  },
  urlArgs: ''
});


var App = (function (App) {
  return App;
}(App || {}));

/* Global sharing (only once necessary) */
window.App = App;

/* Console fallback for ie8 */
window.console = window.console || {
    'log': function () {
    }
  };

if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
  App.mobile = true;
}

// Mobile Breakpoints
App.Breakpoints = {};
App.Breakpoints.mobile = 650;
App.Breakpoints.tablet = 790;

App.guid = (function () {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }

  return function () {
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
      s4() + '-' + s4() + s4() + s4();
  };
})();


require([
  'jquery',
  'lodash',
  'bxslider',
  'tabsToAccordion'
], function ($, _) {

  // for using in templates {{ expression }} Angular.js syntax
  _.templateSettings.interpolate = /\{\{(.+?)\}\}/g;

  // {{-imgUrl}} - insert as text
  _.templateSettings.escape = /\{\{\-(.+?)\}\}/g;

  // do not try use <% func() %> syntax - it not working. Working [[ ]] syntax
  _.templateSettings.evaluate = /\[\[(.+?)\]\]/g;

  require(['router']);
});
