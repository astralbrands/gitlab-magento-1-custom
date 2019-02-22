define([
  'jquery',
  'backbone',
  'modal',
  'shadeguideModal'
], function ($, Backbone, modal, shadeguideModal) {

  'use strict';

  var filterSlider;

  var ListingView = Backbone.View.extend({
    initialize: function () {
      var self = this;
      var filters = $('#filters-wrapper');
      var btnCloseOpen = $('.btnx');
      var sliderWrapper = jQuery('#best-match-slider');
      var btnCloseMobile = $('.filters__mobile-close');
      var otherFilters = $('#other-filters');
      var colorFilters = $('#color-filters');
      var filteredLine = $('#filtered-items-line');
      var filterSliderItems = $('.filter-slider-item');
      var body = $('body');

      this.bindOpenFilter_desktop = function () {
        btnCloseOpen.off('click.closeOpen');
        btnCloseOpen.on('click.closeOpen', function (e) {
          e.preventDefault();
          $('.toolbar').find('.btnx').toggleClass('btn-open');
          filters.toggleClass('filters-open');
        });
      };

      this.bindOpenFilter_mobile = function () {
        btnCloseOpen
          .off('click.closeOpen')
          .on('click.closeOpen', function (e) {
            e.preventDefault();
            $('.toolbar').find('.btnx').toggleClass('btn-open');
            filters.toggleClass('filters-open');
            $('.toolbar').find('.btnx').hasClass('btn-open') ? body.css('overflow', 'hidden') : body.css('overflow', 'auto');
          });

        btnCloseMobile
          .off('click.closeOpen')
          .on('click.closeOpen', function (e) {
            e.preventDefault();
            $('.toolbar').find('.btnx').toggleClass('btn-open');
            btnCloseOpen.removeClass('btn-open');
            filters.toggleClass('filters-open');
            body.css('overflow', 'auto');
          });

        otherFilters.find('.filter-title').off('click.custom');
        otherFilters.find('.filter-title').on('click.custom', function (e) {
          $(this).parent().toggleClass('open-filter-mobile').siblings().removeClass('open-filter-mobile');
          colorFilters.removeClass('open-filter-mobile');
        });

        colorFilters.find('.filter-title').off('click.custom');
        colorFilters.find('.filter-title').on('click.custom', function (e) {
          $(this).parent().toggleClass('open-filter-mobile');
          otherFilters.find('.open-filter-mobile').removeClass('open-filter-mobile');
        })

      };

      if (filters.hasClass('no-filters')) {
        $('.main-container').addClass('without-filter'); // todo remove this
      }

      filteredLine.insertAfter('.category-tools').show();
      filters.insertBefore('#filtered-items-line');

      if (otherFilters.find('#category-filters').length === 0) {
        this.cloneCategoryFilter = $('#category-filters').clone().addClass('categories-mobile');
        this.cloneCategoryFilter.prependTo('#other-filters');

        this.cloneCategoryFilter.find('.title').off('click.expand');
        this.cloneCategoryFilter.find('.title').on('click.expand', this, function (e) {
          e.data.cloneCategoryFilter.toggleClass('open-filter-mobile').siblings().removeClass('open-filter-mobile');
          colorFilters.removeClass('open-filter-mobile');
        });

        this.cloneCategoryFilter.find('li').off('click.applyFilter');
        this.cloneCategoryFilter.find('li').on('click.applyFilter', function (e) {
          $(this).find('a').addClass('category-checked');
          filters.append('<div class="partial-overlay"><div>')
        });
      }

      if (filterSliderItems.length > 0) {
        //var allCachedItems = filterSliderItems.remove().clone().removeClass('filter-slider-item');
        var allCachedItems = filterSliderItems.clone().removeClass('filter-slider-item');
        filterSliderItems.remove();
        if (allCachedItems.filter('.show-on-ajax-load').length > 0) {
          allCachedItems.filter('.show-on-ajax-load').appendTo(sliderWrapper);
          $('.color-match').show();
          self.isMobile ? self.initFilterSliderMobile() : self.initFilterSlider();
        }else{
          $('.color-match').hide();
        }
      }
      else {
        colorFilters.hide();
        $('#other-filters').addClass('no-color-filters');
      }

      this.bindEvents = function (e) {
        var self;
        if (e) {
          self = e.data;
        }
        else {
          self = this;
        }

        // desktop
        if (window.innerWidth > 768) {
          self.isMobile = false;
          self.initDesktopFilter(self);
        }

        // mobile
        if (window.innerWidth <= 768) {
          self.isMobile = true;
          self.initMobileFilter(self);
        }

        $('input[name="color-range"]')
          .off('click.initSlider')
          .on('click.initSlider', self, function (e) {
            e.data._destroySlider();
            sliderWrapper.find('li').remove();
            var selectedShade = $(this).attr('id');
            $('.triangle').hide();
            $(this).siblings('.triangle').show();
            var filteredItems = allCachedItems.filter('.' + selectedShade);
            if (filteredItems.length > 0) {
              $('.color-match').show();
              filteredItems.appendTo(sliderWrapper);
              e.data.isMobile ? e.data.initFilterSliderMobile() : e.data.initFilterSlider();
              self.initFiltres();
            }
          });
      };

      this.initDesktopFilter = function (self) {
        self.bindOpenFilter_desktop();
        self.cloneCategoryFilter.hide();
      };

      this.initMobileFilter = function (self) {
        self.bindOpenFilter_mobile();
        self.cloneCategoryFilter.show();
      };

      this.initFilterSliderMobile = function () {
        this._destroySlider();
        sliderWrapper = jQuery('#best-match-slider');
        var sliderOptions = {
          slideSelector: 'li.item',
          slideWidth: 118,
          swipeThreshold: 100,
          slideMargin: 15,
          touchEnabled: false,
          minSlides: 2,
          maxSlides: 2,
          moveSlides: 2,
          infiniteLoop: true,
          pager: false,
          controls: false
        };
        if (sliderWrapper.find('li.item').length > 2) {
          sliderOptions.controls = true;
          sliderOptions.touchEnabled = true;
        }
        filterSlider = sliderWrapper.bxSlider(sliderOptions);
      };

      this.initFilterSlider = function () {
        this._destroySlider();

        sliderWrapper = jQuery('#best-match-slider');
        var sliderOptions = {
          slideSelector: 'li.item',
          slideWidth: 126,
          slideMargin: 26,
          swipeThreshold: 100,
          minSlides: 4,
          maxSlides: 4,
          moveSlides: 4,
          infiniteLoop: true,
          pager: false,
          controls: false
        };
        if (sliderWrapper.find('li.item').length > 4) {
          sliderOptions.controls = true;
          sliderOptions.touchEnabled = true;
        }
        filterSlider = sliderWrapper.bxSlider(sliderOptions);
      };

      this._destroySlider = function () {
        if (sliderWrapper.find('li').length > 0) {
          if (filterSlider) {
            filterSlider.destroySlider();
            //colorFilters.find('.bx-wrapper').remove();
          }
        }
      };

      if (sliderWrapper.find('li').length === 0 && this.filterSlider) {
        this._destroySlider();
        $('.color-filter__header').hide();
      }

      $(window).on('resize', this, this.bindEvents);

      function onFilterCompleted() {
        self.initialize();
      }

      document.removeEventListener('filter-appended', onFilterCompleted);
      document.addEventListener('filter-appended', onFilterCompleted);

      modal();

      this.initFiltres();

      $('[data-modal-name = "shade-guide"]')
        .off('mcallback')
        .on('mcallback', shadeguideModal.bind($(this)));

      this.bindEvents();

    },
    initFiltres: function () {
      $('.color-range-item').each(function(index, item) {
        var colorBlock = $(item).find('div.input-box');
        var backgroundForBlock = $(item).data('color-hex');
        var triangle = $(item).find('.triangle');
        triangle.css('background', '#' + backgroundForBlock);
        colorBlock.css('background', '#' + backgroundForBlock);
      });

      var listingView = this;
      $('.input-box.filter').find('label.label-text').on('click', filterClick);
      $('.image-box').on('click', filterClick);

      function filterClick () {
        var url = $(this).attr('data-href') || $(this).closest('li').find('label.label-text').attr('data-href');
        var checkBox = $(this).closest('li').find('input[type="checkbox"]');
        checkBox.prop("checked", !checkBox.prop("checked"));
        $('.overlay').addClass('filter-overlay');
        var loader = $('<img>');
        loader.addClass('loader');
        loader.attr('src', '../../skin/frontend/born/born/images/PUR_loader.gif');
        $('.overlay').append(loader);
        $.get(url, function (data) {})
          .done(function (data) {
            $('.overlay').removeClass('filter-overlay');
            $('.loader').remove();
            var categoryView = $(data).find('.main-container');
            categoryView.find('#filters-wrapper').addClass('filters-open');
            categoryView.find('.btnx').addClass('btn-open');
            $('body').find('.main-container').replaceWith(categoryView);
            require(["pages/common"], function (common) {
              common.initialize();
            });
            listingView.initialize();
          })
          .fail(function () {
          });
      }
    }
  });
  return new ListingView();
});
