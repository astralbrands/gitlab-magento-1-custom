

(function ( $, window, document, undefined ) {

    var pluginName = 'verticalCarousel',
        defaults = {
            propertyName: "value"
        };

    // The actual plugin constructor
    function Plugin( element, options ) {
        this.element = element;

        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();

    }

    Plugin.prototype.init = function () {

        var container = $(this.element);

        //container.html('<div class="owl-wrapper-outer"><div class="owl-wrapper">'+container.html()+'</div></div>');

        container.wrapInner('<div class="owl-wrapper"></div>').wrapInner('<div class="owl-wrapper-outer"></div>');

        //wrap('<div class="owl-carousel"></div>').wrap('<div class="owl-wrapper-outer"></div>').wrap('<div class="owl-wrapper"></div>').unwrap();

        var outer = container.children('.owl-wrapper-outer');
        var inner = outer.children('.owl-wrapper');

        inner.show();

        setTimeout(
            function()
            {
                outer.height($('#product-image').height()-2*$('.owl-dir').height());
            }, 1000);


    };

    Plugin.prototype.scroll = function (direction) {

        var outer = $(this.element).children('.owl-wrapper-outer');
        var inner = outer.children('.owl-wrapper');
        var items = inner.children('.item');

        // get item on top

        var itemOnTop = 0;
        var marginTop = parseInt(inner.css('marginTop'));
        var currentItem = 0;

        while (marginTop < 0) {
            currentItem = $(items[itemOnTop]);
            marginTop += currentItem.height() +
                         parseInt(currentItem.css('paddingTop')) +
                         parseInt(currentItem.css('paddingBottom')) +
                         parseInt(currentItem.css('marginTop')) +
                         parseInt(currentItem.css('marginBottom'));
            itemOnTop++;
        }

        // calculate scroll value

        var scrollValue = 0;

        if (direction=="down") {
            itemOnTop -= 2;
        }

        for(var j = 0; j<=(itemOnTop); j++) {
            currentItem = $(items[j]);
            scrollValue += currentItem.height() +
                           parseInt(currentItem.css('paddingTop')) +
                           parseInt(currentItem.css('paddingBottom')) +
                           parseInt(currentItem.css('marginTop')) +
                           parseInt(currentItem.css('marginBottom'));
        }

        if ((direction=="down") && (parseInt(inner.css('marginTop')) != 0)) {
            inner.css('marginTop',parseInt(inner.css('marginTop'))+1+'px');
        }

        if((inner.height() - scrollValue) < outer.height()) {
            scrollValue = Math.abs(outer.height()-inner.height());
        }

        if((inner.height() + parseInt(inner.css('marginTop'))) > outer.height()) {
            //inner.css('marginTop',-scrollValue+'px');
            inner.animate({'margin-top':-scrollValue+'px' },500);
        }

    };

    Plugin.prototype.down = function () {
        console.log('downin');
    };

    Plugin.prototype.items = function () {
        inner.child('item');
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin( this, options ));
            }
        });
    }



})( jQuery, window, document );

