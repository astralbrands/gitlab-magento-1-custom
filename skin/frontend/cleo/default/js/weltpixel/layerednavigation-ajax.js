var Weltpixel_LayeredNavigation_Ajax = Weltpixel_LayeredNavigation_Ajax || {};

Weltpixel_LayeredNavigation_Ajax.init = function(options) {
    this.options = options;
    this.url = '';
    this.ajaxRequest = null;
    this.disableClicks = this.options.disableClicks;
    this.allowAjax = true; //allow next ajax call only when the previous is finished
    this.bindAjaxTriggers();
    this.firstCall = false;
    this.bindPopState(); // if you want to make ajax call also when you click back in the browser to a previous ajax call
};

Weltpixel_LayeredNavigation_Ajax.bindAjaxTriggers = function() {
    $$('.block-layered-nav a').invoke('observe', 'click', this.prepareCall.bind(this));
    $$('.block-layered-nav input').invoke('observe', 'click', this.prepareCall.bind(this));
    $$('.pages li a', '.view-mode a', '.sorter a').invoke('observe', 'click', this.prepareCall.bind(this));
    $$('.filter_checkbox').invoke('removeAttribute', 'onclick');
    $$('.limiter select', '.sorter select', '.weltpixel-layered-select').invoke('removeAttribute', 'onchange');
    $$('.limiter select', '.sorter select', '.weltpixel-layered-select').invoke('observe', 'change', this.prepareCall.bind(this));

    if (typeof ConfigurableSwatchesList != "undefined") {
        ConfigurableSwatchesList.init();
    }


    $$('.toggleSign').invoke('observe','click', this.toggleCategoryTree.bind(this));

    if (this.firstCall) {
        this.options.triggercallback();
    }

    /* scroll to top of the page */
    window.scrollTo(0, 0);

    //this.mobileEvents();

    this.allowAjax = true;
};

/*
Weltpixel_LayeredNavigation_Ajax.mobileEvents = function() {

    $$('.filter-button, .filters-close').invoke('observe','click',function(event){
        $$('.block-layered-nav').each(function(c) {
            if( c.hasClassName('hidden') ){
                c.removeClassName('hidden');
            }
            else {
                c.addClassName('hidden');
            }
        });
    });

    $$('.filters-close').invoke('observe','click',function(event){
        //        Event.observe($$('.trigger-sign')[0],'click',function(){});
        //        fireEvent($$('.trigger-sign')[0],'click');
        $$('.trigger-sign')[0].select('i').each(function(x) {
            if(x.hasClassName('ion-ios7-minus-empty') == true) {
                if (x.hasClassName('ion-funnel') == false) {
                    x.removeClassName('ion-ios7-minus-empty').addClassName('ion-ios7-plus-empty');
                }
            }
            else {
                if (x.hasClassName('ion-funnel') == false) {
                    x.removeClassName('ion-ios7-plus-empty').addClassName('ion-ios7-minus-empty');
                }

            }
        });

    });


    $$('.filter-name').invoke('observe','click',function(event){
        var element = event.findElement('p');
        if(!element.next().hasClassName('current'))
        {
            $$('.filter-container').each(function(p) {
                p.select('.current').each(function(x) {
                    x.removeClassName('current');
                });
            });
            element.addClassName('current');
            element.next().addClassName('current');
        }
    });

    $$('.trigger-sign').invoke('observe','click',function(event){
        $(this).select('i').each(function(x) {
            if(x.hasClassName('ion-ios7-minus-empty') == true) {
                if (x.hasClassName('ion-funnel') == false) {
                    x.removeClassName('ion-ios7-minus-empty').addClassName('ion-ios7-plus-empty');
                }
            }
            else {
                if (x.hasClassName('ion-funnel') == false) {
                    x.removeClassName('ion-ios7-plus-empty').addClassName('ion-ios7-minus-empty');
                }

            }
        });

        if($(this).next().hasClassName('trigger-content') == true) {
            $(this).next().toggle();
        }
        else {
        //$(this).select('trigger-content').toggle();
        }
    });
};
*/

Weltpixel_LayeredNavigation_Ajax.bindPopState = function() {
    window.onpopstate = function(event) {
        if (event && event.state) {
            Weltpixel_LayeredNavigation_Ajax.makeCall(document.location.href);
        }
    };
};

Weltpixel_LayeredNavigation_Ajax.toggleCategoryTree = function(event) {
    toggleElement = Event.element(event);
    $(toggleElement).up('li').down('ul').toggle();

    var toggleSign = $(toggleElement).innerHTML;
    if (toggleSign == '-') {
        toggleSign = '+';
    } else {
        toggleSign = '-';
    }
    $(toggleElement).update(toggleSign);
};


Weltpixel_LayeredNavigation_Ajax.prepareCall = function(event) {
    event.preventDefault();
    if (!this.allowAjax) return true;
    triggeredElement = Event.element(event);
    tagType = triggeredElement.tagName.toLowerCase();
    url = '';


    if (triggeredElement.hasClassName('ignore-ajax')) {
        return true;
    }

    /**
     * This must be extended when the new custom filters will be implemented
     */
    if (tagType == "input") {
        if (triggeredElement.hasClassName('event_reload')) {
            return true;
        }
        if (triggeredElement.hasClassName('filter_checkbox')) {
            url = triggeredElement.next().href;
        }
        else if (triggeredElement.hasClassName('force_reload')) {
            url = triggeredElement.value;
        } else {
            url = triggeredElement.next().value;
        }
    } else if (tagType == "select" && triggeredElement.value) {
        url = triggeredElement.value;
    } else if (tagType == "a" && triggeredElement.href) {
        url = triggeredElement.href;
    } else {
        triggeredElement = Event.findElement(event, 'a');
        url = triggeredElement.href;
    }

    /**
    * If url is valid make the call
    */
    if (url != "#" || url !== '') {
        this.makeCall(url);
        /**
         * Set it to false if from backend no further filters are allowed during an ajaxcall
         */
        if (this.disableClicks) {
            this.allowAjax = false;
        }
    }

    return true;
};

/**
 * Extra loading icon with a div layer can also be added
 */
Weltpixel_LayeredNavigation_Ajax.animationBeforeCall = function() {
    $$('div.category-products').each(function (item) {
        item.addClassName('ajax-layered-loading');
    });
    $$('.block-layered-nav').each(function (item) {
        console.log('???');
        item.addClassName('ajax-layered-loading hidden');
    });
};

Weltpixel_LayeredNavigation_Ajax.makeCall = function(url) {
    if (this.ajaxRequest) {
        this.ajaxRequest.transport.abort();
    }
    this.animationBeforeCall();
    if ($$('.filter-button')[0]) {
        Event.observe($$('.filter-button')[0],'click',function(){});
        fireEvent($$('.filter-button')[0],'click');
    }
    this.url = url;
    params = {};
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: params,
        onSuccess: this.processResult.bind(this),
        onComplete: this.bindAjaxTriggers.bind(this) //rebind the events to the newly returned data
    });
};

Weltpixel_LayeredNavigation_Ajax.processResult = function(transport) {
    var response = transport.responseText || null;
    if (response != null)
    {
        var responseElement = new Element('div');
        responseElement.update(response);

        var listContainer = responseElement.select('div#weltpixel-ajax-list-container')[0];
        var filterContainer = responseElement.select('div#weltpixel-ajax-filters-container')[0];

        if ($$('div.category-products').length) {
            $$('div.category-products').each(function (item) {
                Element.replace(item, listContainer.innerHTML);
            });
        } else {
            //no result was returned
            $$('.note-msg').each(function (item) {
                Element.replace(item, listContainer.innerHTML);
            });
        }

        $$('.block-layered-nav').each(function (item) {
            //remove element from dom that are not needed, or are duplicated
            if (filterContainer.select('.filter-button')[0]) {
                filterContainer.select('.filter-button')[0].remove();
            }
         //   filterContainer.select('.filter-button')[0].remove();
            Element.replace(item, filterContainer.innerHTML);
            customJs.init();
        });

        this.firstCall = true;
        //push Url in history and browser bar, if url shouldn't be changed this can be removed
        history.pushState({
            url: this.url
        }, document.title, this.url);
    };
};
