function SkuLookup() {}

SkuLookup.prototype = {

    /**
     * Takes a product sku, and adds the product with that sku to the magento cart
     * @param string | number productSku Sku of the product you wish to add.
     * @param number qty Quantity you want to add to the cart
     * @param Event e Event that called the function
     */
    addProductToCart: function(productSku, qty, e) {
        var scope = this;
        var button = e.target || e.srcElement || {};
        var hostname = window.location.hostname;
        if (qty < 1) {
            alert('Invalid Qty');
            return;
        }
        button.disabled = true;

        return this.getProductId(productSku).then(function(success) {
            if (success.status==200) {
                var productId = success.data.product_id;
                var formkey = success.data.form_key;
                if (hostname.includes('purcosmetics')) {
                    scope.wpAddToCart(productId, qty, formkey, button);
                } else if (hostname.includes('cosmedix')) {
                    scope.cosAddToCart(productId, qty, button);
                }
            } else {
                alert('Server Lookup Error');
                console.log('success');
            }
        }, function(errorResponse) {
            alert('Server Lookup Error');
            console.log('errorResponse');
        });
    },

    /**
     * Add Product to the cart and rebuild mini cart and cart counter
     * @param number productId Id of the product
     * @param number qty Quantity you want to add to the cart
     * @param Html Input Element button Element used to add the item to the cart
     */
    wpAddToCart: function(productId, qty, formkey, button) {
        var path = window.location.pathname.split('/',2);
        
        var url = '/weltpixel_quickview/ajax_checkout_cart/add/product/' + productId + '/form_key/' + formkey + '/';
        if (typeof qty == 'undefined') {
            qty = 1;
        }

        var addOptions = {};
        addOptions.submittedForm = false;
        addOptions.confirmationHide  = 5;

        data = {}
        data.form_key = formkey;
        data.product = productId;
        data.qty = qty;

        if (typeof window.weltpixel.quickview.addToCart == 'function') {
                window.weltpixel.quickview.addToCart(url, data, addOptions);
        } else {
            alert("Item could not be added to cart");
            console.log('weltpixel is undefined');
        }
        
    },
    /**
     * Add Product to the cart and rebuild mini cart and cart counter
     * @param number productId Id of the product
     * @param number qty Quantity you want to add to the cart
     * @param Html Input Element button Element used to add the item to the cart
     */
    cosAddToCart: function(productId, qty, button) {
        var requestUrl = '/bornajax/checkout_cart/add/';

        var path = window.location.pathname.split('/',2);

        if (path[1] === 'pro') {
            requestUrl = '/pro/bornajax/checkout_cart/add/';       
        }

        if (typeof qty == 'undefined') {
            qty = 1;
        }
        return $j.ajax({
            'url': requestUrl,
            'data': {
                'product': productId,
                'qty': qty,
            },
            'dataType': 'json',
            'success': function(data) {
                if (data.status !== 'SUCCESS') {
                    if (typeof data.message !== 'undefined') {
                        alert('Server Cart Error: ' + data.message);
                    } else {
                        alert('Server Cart Error');
                    }
                    button.disabled = false;
                    return;
                }
                var timeForMinicart = 400; //ms
                var popup = $j('#popup-minicart');

                $j('#toggleMiniCartBtn').find('.count').html(data.cart_qty);
                $j('#toggleMiniCartBtn').removeClass('no-count');
                $j('.show-on-mobile.skip-cart').find('.count').html(data.cart_qty);
                $j('.show-on-mobile.skip-cart').removeClass('no-count');
                if (window.innerWidth > 768) {
                    $j('#popup-minicart').html(data.result_html);
                    if (!$j('#popup-minicart').hasClass('skip-active')) {
                        $j('.overlay').fadeOut('slow');
                        $j('.overlay').find('.modal').hide();
                        popup.addClass('slide-minicart').css('display', 'block')
                            .addClass('skip-active')
                            .animate({'right': 0}, timeForMinicart);
                        $j('body').css('overflow', 'scroll');
                        $j('body').addClass('overlay-menu');
                    }
                    $j('.menu-overlay-element, span.btn-minicart-toggle').on('click', function () {
                        popup.animate({"right": "-425"}, timeForMinicart,
                            function () {
                                popup.removeClass('skip-active');
                                $j('body').removeClass('overlay-menu');
                            });
                    });
                } else {
                    $j(button).addClass('added-successfully');
                    $j(button).text('Added to Bag');
                }
                button.disabled = false;
            },
            'error': function(error) {
                alert('Server Cart Error');
                return;
            }
        });
    },
    /**
     * Looks up the product id given a sku
     * @param  string | number productSku Sku of the product
     * @return {promist} JSON The Sku and Id of the product
     */
    getProductId: function (productSku) {
        var deferred = $j.Deferred();
        var requestUrl = '/astral-feeds/ProductIdLookup';
        $j.ajax({
            'url': requestUrl,
            'data': {
                'product-sku': productSku,
            },
            'dataType': 'json',
            'success': function (response) {
                deferred.resolve(response);
            },
            'error': function (response) {
                deferred.reject(response);
            },
            'timeout': 5000,
        });
        return deferred.promise();
    }
}