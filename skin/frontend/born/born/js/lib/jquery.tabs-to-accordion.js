(function($){
    $.fn.tabsToAccordion = function(options) {
        console.log('tabsToAccordion init');
        this.default = {
            addClassForLastTab: true,
            lastTabClass: 'tab-last'
        };
        $.extend(this.default, options);

      var isMobile = window.innerWidth <= 720;

        var $tabsToAccordion = this,
            $contentArr = $tabsToAccordion.find('.tab-content'),
            $contentFirst = $tabsToAccordion.find('.tab-content:first'),
            $tabContentContainer = $tabsToAccordion.find('.tab-container'),
            $tabs = $tabsToAccordion.hasClass('tabs-to-accordion') ? $tabsToAccordion.find('.tabs') : $tabsToAccordion.find('.tabs-bazaar'),
            $tabArr = $tabs.find('li'),
            $drawerHeadingsArr = $tabsToAccordion.find('.tab-drawer-heading');
        // set view

        if ($tabsToAccordion.hasClass('tabs-to-accordion')) $contentArr.hide();
        if (window.innerWidth > 720 || $tabsToAccordion.hasClass('tabs-to-accordion-bazaar')) {
          $contentFirst.show();
        }

        // set events

        // tab mode
        $tabArr.on('click.tabBehavior', function() {
            var activeTab = $(this).attr("rel");
            $contentArr.hide();
            $tabContentContainer.find('.' + activeTab).fadeIn();
            $tabArr.removeClass('active');
            $(this).addClass('active');
            $drawerHeadingsArr.removeClass('d-active');
            $(".tab-drawer-heading[rel^='"+activeTab+"']").addClass('d-active');
        });

        // drawer mode

        $drawerHeadingsArr.on('click.accordionBehavior', function() {
            var dActiveTab = $(this).attr('rel'),
                destination;
            $contentArr.not('.' + dActiveTab).hide();
            $tabContentContainer.find('.' + dActiveTab).toggle();
            $drawerHeadingsArr.not(this).removeClass('d-active');
            $(this).toggleClass('d-active');
            $tabArr.not(this).removeClass('active');
            $tabs.find("li[rel^='" + dActiveTab + "']").toggleClass('active');
            destination = $(this).offset().top;
            $('body,html').animate({ scrollTop: destination }, 500);
        });

        // optional

        if (this.default.addClassForLastTab) {
            $tabArr.last().addClass(this.default.lastTabClass);
        }

      $(window).on('resize', function () {
        if (isMobile && window.innerWidth > 720) {
          $contentFirst.show();
          isMobile = false;
        }
        if (!isMobile && window.innerWidth <= 720) {
          $contentFirst.hide();
          isMobile = true;
        }
      });

    };
})(jQuery);
