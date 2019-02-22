var hostname = window.location.hostname;
var isPur = false;
var isCos = false;
if(hostname.indexOf('purcosmetics') !== -1){
	isPur = true;
	var cdn = 'https://cf.astralbrandsmedia.com';
}
if(hostname.indexOf('cosmedix') !== -1){
	isCos = true;
	var cdn = 'https://cf.astralbrandsmedia.com';
}

require.config({
  waitSeconds: 0,
  baseUrl: cdn+'/skin/frontend/rwd/born/js/app',
  paths: {
    'lodash': '../../born/js/lib/lodash.min',
    'backbone': '../../born/js/lib/backbone',
    'jquery': '../../born/js/lib/jquery',
    'jqueryUI': '../../born/js/lib/jquery-ui.min',
    'jqueryFormstyler': '../lib/jquery.formstyler.min',
    'text': '../../born/js/lib/text',
    'bxslider': '../../born/js/lib/bxslider',
    'owlslider': '../../born/lib/owl/owl.carousel.min/',
    'tabsToAccordion': '../../born/js/lib/jquery.tabs-to-accordion',
    'markerwithlabel': '../../born/js/lib/markerwithlabel',
    'browserDetect': '../../born/js/components/browser-detect',
    'modal': '../../born/js/components/modal',
    'wishlist': '../../born/js/components/wishlist',
    'shippingModal': '../../born/js/app/modals/shipping-returns',
    'quickviewModal': '../../born/js/app/modals/quickview',
    'quickviewModalPHP': '../../born/js/app/modals/quickviewPHP',
    'lookModal': '../../born/js/app/modals/look',
    'shadeguideModal': '../../born/js/app/modals/shade-guide',
    'fixedHeader': '../../born/js/app/modules/fixed-header',
    'backToTop': '../../born/js/app/modules/back-to-top',
    'headerHoverThumbnail': '../../born/js/app/modules/header-hover-thumbnail',
    'headerHoverThumbnail_cos': '/skin/frontend/rwd/born_cos/js/app/modules/header-hover-thumbnail_cos',
    'menuFlyoutsPosition': '/skin/frontend/rwd/born_cos/js/app/modules/menu-flyouts-position',
    'routineSlider': '../../born/js/app/modules/routine-slider',
    'routineSlider_cos': '/skin/frontend/rwd/born_cos/js/app/modules/routine-slider_cos',
    'mainProductSlider': '/skin/frontend/rwd/born_cos/js/app/modules/main-product-slider_cos',
    'pressSlider': '../../born/js/app/modules/press-slider',
    'minicart': '../../born/js/app/modules/minicart',
    'sampleSlider': '../../born/js/app/modules/sample-slider',
    'categorySlider': '../../born/js/app/modules/category-slider',
    'shade': '../../born/js/app/modules/shade',
    'bundleShade': '../../born/js/app/modules/bundle-shade',
    'teamSlider_cos': '/skin/frontend/rwd/born_cos/js/app/modules/team-slider_cos',
    'blogSlider': '../../born/js/app/modules/blog-slider',
    'blogEvents': '../../born/js/app/modules/blog-events',
    'blogArticleSlider': '../../born/js/app/modules/blogarticle-slider',
    'spinner': '../../born/js/components/spinner',
    'shareModalPHP': '../../born/js/app/modals/shareModalPHP',
    'readmoreModalPHP': '../../born/js/app/modals/readmorePHP',
    'eventModalPHP': '../../born/js/app/modals/eventPHP',
    'infobox': '../../born/js/lib/infobox',
    'pgp': '../../born/js/app/pages/pgp',
    'bundle': '../../born/js/app/pages/bundle',
    'undertoneModal': '../../born/js/app/modals/undertone-guide',
    'videoInImage': '../../born/js/app/modules/video-in-image',
    'qtyCounter': '/skin/frontend/rwd/born_cos/js/app/modules/qty-counter',
    'tierpricingModal': '../../born/js/app/modals/tierpricing-modal',
    'proformManualModal': '../../born/js/app/modals/proform-manual-modal',
    'educationModal': '../../born/js/app/modals/educationModal',
    'informationModal': '../../born/js/app/modals/information-modal'
  },
  shim: {
    lodash: {
      exports: '_'
    },
    backbone: {
      deps: ['jquery', 'lodash'],
      exports: 'Backbone'
    },
    fitvids: {
      deps: ['jquery'],
      exports: 'fitvids'
    },
    bxslider: {
      deps: ['jquery', 'fitvids'],
      exports: 'bxslider'
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
      deps: ['jquery']
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
  'bxslider',
  'lodash'
], function ($, bxslider, _) {

  // for using in templates {{ expression }} Angular.js syntax
  _.templateSettings.interpolate = /\{\{(.+?)\}\}/g;

  // {{-imgUrl}} - insert as text
  _.templateSettings.escape = /\{\{\-(.+?)\}\}/g;

  // do not try use <% func() %> syntax - it not working. Working [[ ]] syntax
  _.templateSettings.evaluate = /\[\[(.+?)\]\]/g;

  require(['/skin/frontend/born/born_cos/js/app/router.js']);
});
