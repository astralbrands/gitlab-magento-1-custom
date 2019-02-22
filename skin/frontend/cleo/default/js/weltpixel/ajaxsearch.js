var WeltPixel_AjaxSearch = WeltPixel_AjaxSearch || {};

WeltPixel_AjaxSearch.init = function(url, minChars) {
    this.url = url;
    this.minChars = minChars;
    this.bindEvents();
}

WeltPixel_AjaxSearch.bindEvents = function() {
    this.xhr = null;
    this.c = $('wpas-container');
    this.l = $('wpas-loupe');
    this.f = $('wpas-form');
    this.q = $('wpas-q');
    this.r = $('wpas-ajax');
    this.n = $('wpas-count');
    this.t = $('wpas-loader');
    this.i = $('wpas-items');

    if (this.l) {
        Event.observe(this.l, 'click', this.showSearchBox);
    }

    Event.observe(document, 'touchend', this.hideSearchBox);
    Event.observe(document, 'click', this.hideSearchBox);

    if (this.c) {
        Event.observe(this.c, 'touchend', this.keepSearchBox);
        Event.observe(this.c, 'click', this.keepSearchBox);
    }

    if (this.q) {
        Event.observe(this.q, 'keyup', this.handleKeyUp);
    }

}

WeltPixel_AjaxSearch.showSearchBox = function(e) {
    $$('#page-header').invoke('addClassName', 'search-open');
    if (WeltPixel_AjaxSearch.q && WeltPixel_AjaxSearch.f) {
        WeltPixel_AjaxSearch.q.setValue('');
        WeltPixel_AjaxSearch.f.setStyle({
            'display': 'block'
        });
        WeltPixel_AjaxSearch.q.focus();
    }
}


WeltPixel_AjaxSearch.hideSearchBox = function(e) {
    $$('#page-header').invoke('removeClassName', 'search-open');
    if (WeltPixel_AjaxSearch.f && WeltPixel_AjaxSearch.r) {
        WeltPixel_AjaxSearch.f.setStyle({
            'display': 'none'
        });
        WeltPixel_AjaxSearch.r.setStyle({
            'display': 'none'
        });
    }
}


WeltPixel_AjaxSearch.keepSearchBox = function(e) {
    e.cancelBubble = true;
}

WeltPixel_AjaxSearch.keepSearchBox = function(e) {
    e.cancelBubble = true;
}


WeltPixel_AjaxSearch.handleKeyUp = function(e) {

    var queryLength = WeltPixel_AjaxSearch.q.value.length;

    if (queryLength >= WeltPixel_AjaxSearch.minChars)
    {
        if (WeltPixel_AjaxSearch.xhr) {
            WeltPixel_AjaxSearch.xhr.transport.abort();
        }
        WeltPixel_AjaxSearch.xhr = new Ajax.Request(WeltPixel_AjaxSearch.url, {
            method: 'get',
            parameters : {
                q: WeltPixel_AjaxSearch.q.value
            },
            onLoading: function() {
                WeltPixel_AjaxSearch.t.setStyle({
                    display: 'block'
                });
            },
            onSuccess: function(transport) {
                var response = transport.responseText || null;
                if (response != null)
                {
                    response = response.evalJSON();
                    WeltPixel_AjaxSearch.i.update(response.elements);
                    WeltPixel_AjaxSearch.n.update(response.resultcounts);
                    WeltPixel_AjaxSearch.r.setStyle({
                        display: 'block'
                    });
                    WeltPixel_AjaxSearch.t.setStyle({
                        display: 'none'
                    });
                }
            },
            onFailure: function() {
                alert('Search error...');
            }
        });
    }
    else
    {
        WeltPixel_AjaxSearch.r.setStyle({
            display: 'none'
        });
        WeltPixel_AjaxSearch.t.setStyle({
            display: 'none'
        });
    }
}