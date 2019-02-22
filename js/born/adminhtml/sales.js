AdminOrder.addMethods({
    loadShippingRates : function(){
        this.isShippingMethodReseted = false;
        this.loadArea(['shipping_method', 'totals'], true, {collect_shipping_rates: 1, reset_shipping: 1});
    },
    loadFreeShippingRates : function(){
        this.isShippingMethodReseted = false;
        this.loadArea(['shipping_method', 'totals'], true, {collect_shipping_rates: 1, reset_shipping: 1, free_shipping:1 });
    }
});