    jQuery(document).ready(function(){
            jQuery('.add-to-cart-buttons').click(function(e){
            var sku;
                if (product_type == 'configurable'){
                sku = jQuery("a.checked").attr('data-swatch');
                }
                if (product_type == 'simple'){
                sku = productObject.id;
                }
            dyTriggerAddToCartEvent(sku, productObject.item_price, fobj.currency);                
            });
            
        /**
         * Fires DY Add to Cart Event
         * @param  number sku          [description]
         * @param  number value        [description]
         * @param  string currencyCode [description]
         * @return void
         */
        function dyTriggerAddToCartEvent(sku, value, currencyCode) {
            var quantity = document.getElementById('qty').value;

            DY.API('event', {
                name: 'Add to Cart',
                properties: {
                    'dyType': 'add-to-cart-v1',
                    'value': value, // Total value in actual payment currency, as float (dollars dot cents)
                    'currency': currencyCode, // Optional non-default currency used
                    'productId': sku, // SKU exactly as in the product feed!
                    'quantity': quantity,
                }
            });
        }            
            
            
    });