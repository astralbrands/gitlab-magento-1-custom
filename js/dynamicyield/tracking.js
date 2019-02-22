function DynamicYield_Tracking() {
    jQuery(document).ready(this.onLoad.bind(this));
    this.ajaxEvent(XMLHttpRequest);
}

/**
 * Detects the current page type
 *
 * @returns {*}
 */
DynamicYield_Tracking.prototype.detectPage = function() {
    if(DY_PAGETYPE)
        return DY_PAGETYPE;
    return null;
};

/**
 * Sends a request to Dynamic Yield API
 *
 * @param name
 * @param properties
 */
DynamicYield_Tracking.prototype.callEvent = function(name, properties) {
    properties['uniqueRequestId'] = getUniqueId();
    var eventData = {
        name: name,
        properties: properties
    };
    try { DY.API('event', eventData); } catch(e) {}
};

/**
 * Generates unique id
 *
 * @returns {Number}
 */
function getUniqueId() {
    return parseInt(Date.now() + Math.random());
}

/**
 * Registers all events based on page
 */
DynamicYield_Tracking.prototype.onLoad = function() {
    var type = this.detectPage();

    if(type === 'category') {
        jQuery(DY_SELECTORS.category_page_filters).click(this.onLayeredNavClick.bind(this));
        jQuery(DY_SELECTORS.category_page_sort_options).attr('onchange', ''); // clears `onclick` attributes in the HTML
        jQuery(DY_SELECTORS.category_page_sort_options).change(this.onSortChange.bind(this));
        jQuery(DY_SELECTORS.category_page_sort_order).click(this.onSortChange.bind(this));
    } else if(type === 'product') {
        // Detect type of swatch
        jQuery(DY_SELECTORS.product_page_buttons_swatch).click(this.onProductSwatchClick.bind(this));
        jQuery(DY_SELECTORS.product_page_dropdowns).change(this.onProductAttributeSelectChange.bind(this));
    }
};

/**
 * Getting the relative element based on the structure
 * Structure has to be in JSON type, except regex (for example replace) params should not be strings
 */
DynamicYield_Tracking.prototype.applySelectors = function (target, relations) {
    for(var key in relations) {
        try{
            var index = relations[key].match(/\|return=(\d*)/);
            var trimmedKey = key.replace(/[0-9]/g, '');
            target = relations[key] ? (Array.isArray(relations[key]) ? target[trimmedKey].apply(target,relations[key]) : target[trimmedKey](relations[key])) : target[trimmedKey]();
            if(index != null) {
                target = index ? target[index[1]] : target;
            }
        } catch(e){
            return;
        }
    }
    return target;
};

/**
 * Handles category filter changes
 *
 * @param event
 */
DynamicYield_Tracking.prototype.onLayeredNavClick = function(event) {
    event.preventDefault();
    var self = jQuery(event.currentTarget);
    var filterString = false;

    var name = this.applySelectors(self,DY_CUSTOM_STRUCTURE.category_page_filters_type);
    var value = this.applySelectors(self,DY_CUSTOM_STRUCTURE.category_page_filters_price_value)
             || this.applySelectors(self,DY_CUSTOM_STRUCTURE.category_page_filters_regular_value)
             || this.applySelectors(self,DY_CUSTOM_STRUCTURE.category_page_filters_swatch_value)
             || this.applySelectors(self,DY_CUSTOM_STRUCTURE.category_page_filters_swatch_image_value);

    if (value && value.toString().match(/[a-z]/i)) {
       filterString = true;
    }

    if(name && value) {
        if(!filterString) {
            this.callEvent('Filter Items', {
                dyType: 'filter-items-v1',
                filterType: name,
                filterNumericValue: value
            });
        } else {
            this.callEvent('Filter Items', {
                dyType: 'filter-items-v1',
                filterType: name,
                filterStringValue: value
            });
        }
    }
    setTimeout(function () {window.location = self.attr('href');},500);
};

/**
 * Handles swatch based product attribute switcher
 *
 * @param event
 */
DynamicYield_Tracking.prototype.onProductSwatchClick = function(event) {
    var self = jQuery(event.currentTarget);

    if(!self) {
        return;
    }

    var name = this.applySelectors(self,DY_CUSTOM_STRUCTURE.product_page_swatch_type);
    var value = this.applySelectors(self,DY_CUSTOM_STRUCTURE.product_page_swatch_value);

    if(name && value) {
        this.callEvent('Change Attribute', {
            dyType: 'change-attr-v1',
            attributeType: name,
            attributeValue: value
        });
    }
};

/**
 * Handles select based product attribute switcher
 *
 * @param event
 */
DynamicYield_Tracking.prototype.onProductAttributeSelectChange = function(event) {
    var self = jQuery(event.currentTarget);

    var name = this.applySelectors(self,DY_CUSTOM_STRUCTURE.product_page_attribute_type);
    var value = this.applySelectors(self,DY_CUSTOM_STRUCTURE.product_page_attribute_value);

    if(name && value) {
        this.callEvent('Change Attribute', {
            dyType: 'change-attr-v1',
            attributeType: name,
            attributeValue: value
        });
    }
};

/**
 * Handles sort event
 *
 * @param event
 */
DynamicYield_Tracking.prototype.onSortChange = function(event) {
    event.preventDefault();
    var caller = jQuery(event.currentTarget);

    var sortBy = this.applySelectors(caller,DY_CUSTOM_STRUCTURE.category_page_sort_order_by);
    var sortOrder = this.applySelectors(caller,DY_CUSTOM_STRUCTURE.category_page_sort_order_direction);
    var changingDir = this.applySelectors(caller,DY_CUSTOM_STRUCTURE.category_page_sort_order_switcher);

    if(sortOrder && !Array.isArray(sortOrder)) {
        if(changingDir) {
            changingDir = changingDir.is(caller);
        }
        sortOrder = changingDir ? sortOrder.toUpperCase() : (sortOrder === "desc" ? "ASC" : "DESC");

        if(sortBy && sortOrder) {
            this.callEvent('Sort Items', {
                dyType: 'sort-items-v1',
                sortBy:  sortBy,
                sortOrder: sortOrder
            });
        }
    }
    setTimeout(function () {(caller.attr('href') != undefined) ? window.location = caller.attr('href') : window.location = DynamicYield_Tracking.prototype.applySelectors(caller,DY_CUSTOM_STRUCTURE.category_page_sort_order_action);},500);
};

/**
 * Tracks ajax events and sends a request to Dynamic Yield API
 *
 * @param DY_XHR
 */
DynamicYield_Tracking.prototype.ajaxEvent = function (DY_XHR) {
    var dy_send = DY_XHR.prototype.send;
    var dy_headers = [];
    DY_XHR.prototype.send = function(data) {
        var readyState;
        function onReadyStateChange() {

            if (this.readyState == 4 && this.status == 200) {
                try {
                    var key, name, val;
                    var headers = dy_convertHeaders(this.getAllResponseHeaders());
                    for (key in headers) {
                        val = headers[key];
                        if (!dy_headers[key]) {
                            name = key.toLowerCase();
                            dy_headers[name] = val;
                        }
                    }
                    var targetHeader = 'dyi-event-data';
                    if(dy_headers[targetHeader]) {
                        var json = JSON.parse(headers[targetHeader]);
                        try { DY.API('event', json); } catch (e){}
                    }
                } catch (e) {}
            }
            if (readyState) {
                readyState();
            }
        }
        var dy_convertHeaders  = function(responseHeaders) {
            var header, headers, headerKey, name, headerValue, value, resultKey;
            var result = {};

            switch (typeof responseHeaders) {
                case "object":
                    headers = [];
                    for (headerKey in responseHeaders) {
                        headerValue = h[headerKey];
                        name = headerKey.toLowerCase();
                        headers.push("" + name + ":\t" + headerValue);
                    }
                    return headers.join('\n');
                case "string":
                    headers = responseHeaders.split('\n');
                    for (var i = 0; i < headers.length; i++) {
                        header = headers[i];
                        if (/([^:]+):\s*(.+)/.test(header)) {
                            name = (resultKey = RegExp.$1) != null ? resultKey.toLowerCase() : void 0;
                            value = RegExp.$2;
                            if (result[name] == null) {
                                result[name] = value;
                            }
                        }
                    }
                    return result;
            }
        };
        if (this.addEventListener) {
            this.addEventListener("readystatechange", onReadyStateChange, false);
        } else {
            readyState = this.onreadystatechange;
            this.onreadystatechange = onReadyStateChange;
        }
        dy_send.call(this, data);
    }
};

DY = DY || {};

DY.Tracker = new DynamicYield_Tracking();