var Weltpixel_Productswatch = Weltpixel_Productswatch || {};

Weltpixel_Productswatch.init = function(options, preselectedValues) {
    this.options = options;
    this.activation = options.displayOptions;
    this.images = options.displayValues;
    this.initSwatches();
    this.repopulateSwatches();
    this.addChangeEvent();
    if (preselectedValues) {
        this.prepopulate();
    }

}

Weltpixel_Productswatch.addChangeEvent = function() {
    var that = this;
    jQuery(".super-attribute-select").on('change', function() {
        that.initSwatches();
        that.repopulateSwatches();
    });
}

Weltpixel_Productswatch.initSwatches = function() {
    jQuery('.product_swatch').remove();
    var wpobj = this;
    jQuery(".super-attribute-select").each(function() {
        var dropDown = jQuery(this);
        var dropDownId = jQuery(this).attr('id').replace('attribute', '');
        /**
         * Apply just for the enabled attributes
         */
        if (wpobj.activation[dropDownId] != 0) {

            dropDown.css('visibility', 'hidden');
            dropDown.css('width', '0px');
            dropDown.css('position', 'absolute');
            dropDown.find("option[value!='']").each(function() {
                var option = jQuery(this);
                /**
                 * if don't want to show the empty choose option field
                 */
                /* if (!parseInt(option.val())) {
                    return;
                } */

                jQuery("<img>",
                {
                    'src': wpobj.images[option.val()],
                    'alt': option.text()
                }).prependTo(
                    jQuery("<a>", {
                        'href': '#',
                        'class': 'product_swatch',
                        'data-id': dropDown.attr('id'),
                        'data-name': dropDown.attr('name'),
                        'data-value': option.val(),
                        'data-label': option.text(),
                        click: function() {
                            dropDown.val(option.val());
                            var obj = dropDown.get();
                            if (!option.val() || !parseInt(option.val())) {
                                productAddToCartForm.submit(this);
                                return false;
                            }
                            Event.observe(obj[0],'change',function(){});
                            fireEvent(obj[0],'change');
                            wpobj.initSwatches();
                            jQuery(".super-attribute-select").each(function() {
                                if (!parseInt(option.val())) {
                                    /** Trigger the addTocart to show the error message */
                                    productAddToCartForm.submit(this);
                                    return false;
                                }
                                if(parseInt(jQuery(this).val()) && jQuery(this).val()) {
                                    jQuery(".product_swatch[data-value="+jQuery(this).val()+"]").addClass('product_swatch_selected');
                                }
                            });
                            return false;
                        }
                    }).insertBefore(dropDown));
            });
        }
    });
}

Weltpixel_Productswatch.repopulateSwatches = function() {
    jQuery(".super-attribute-select").each(function() {
        if(parseInt(jQuery(this).val()) && jQuery(this).val()) {
            jQuery(".product_swatch[data-value="+jQuery(this).val()+"]").addClass('product_swatch_selected');
        }
    });
}


Weltpixel_Productswatch.prepopulate = function() {
    jQuery(".super-attribute-select").each(function() {
        //it is preselected, likely at edit product from cart

        if (parseInt(jQuery(this).val())){
            return;
        }
        var optFirstValue = jQuery(this).find('option:nth-child(2)').val();

        jQuery(this).val(optFirstValue);

        if(document.createEvent)
        {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent('change', true, true );
            jQuery(this).get(0).dispatchEvent(evt);

        } else {
            var evt = document.createEventObject();
            jQuery(this).get(0).fireEvent('onChange',evt);
        }



    });
    this.repopulateSwatches();
}
