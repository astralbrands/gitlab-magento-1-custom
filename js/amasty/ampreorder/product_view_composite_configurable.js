/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Preorder
 */

var PreorderNoteConfigurable = Class.create({
    initialize : function () {
        this.message = null;
        this.container = null;
        this.isVisible = false;
        this.originalContainerInnerHtml = null;
    },

    disable : function () {
        if (!this.isVisible) {
            return;
        }
        this.getContainer().innerHTML = this.originalContainerInnerHtml;
        this.isVisible = false;
    },

    enable : function () {
        var container = this.getContainer();
        if (!container) {
            return;
        }
        if (!this.isVisible) {
            this.originalContainerInnerHtml = container.innerHTML;
        }
        
        if (this.message) {
            container.innerHTML = this.generateElement();
            this.isVisible = true;
        }
    },

    generateElement : function () {
        return '<span class="ampreorder_note">' + this.message + '</span>';
    },

    getContainer : function () {
        if (!this.container) {
            this.container = $$('.availability span:not(.label)')[0];
        }

        if (!this.container) {
            this.container = $$('.availability')[0];
        }

        return this.container;
    },

    refresh : function (message) {
        this.message = message;
        if (this.isVisible) {
            this.disable();
            this.enable();
        }
    }
});

var PreorderStateConfigurable = Class.create(PreorderState, {
    initialize : function($super) {
        $super();
        this.preorderNote = new PreorderNoteConfigurable();
        this.messageMap = null;
        this.cartLabelMap = null;
    },

    bindEvents : function () {
        var preorderState = this;

        if (typeof spConfig == 'object' && typeof spConfig.configureSubscribe == 'function') {
            spConfig.configureSubscribe(function () {
                preorderState.update();
            });
        } else {
            $$('.super-attribute-select').each(function(select) {
                Event.observe(select, 'change', function() { preorderState.update() });
            });
        }

        // Disable Magento Configurable Swatches hover reactions
        if(typeof(Product.ConfigurableSwatches) == 'function') {
            Product.ConfigurableSwatches.prototype.setStockStatus = function (inStock, simpleProductId) {
                if (!inStock) {
                    this._E.availability.each(function(el) {
                        var el = $(el);
                        el.addClassName('out-of-stock').removeClassName('in-stock');
                        el.select('span:not(.label)').invoke('update', Translator.translate('Out of Stock'));
                    });
                    this._E.cartBtn.btn.each(function(el) {
                        var el = $(el);
                        el.addClassName('out-of-stock');
                        el.disabled = true;
                        el.removeAttribute('onclick');
                        el.observe('click', function(event) {
                            Event.stop(event);
                            return false;
                        });
                        el.writeAttribute('title', Translator.translate('Out of Stock'));
                        el.select('span span').invoke('update', Translator.translate('Out of Stock'));
                    });
                } else {
                    var isPreorder = false;
                    if (simpleProductId && preorderState.preorderMap[simpleProductId]) {
                        preorderState.cartLabel = preorderState.cartLabelMap[simpleProductId];
                        preorderState.preorderNote.refresh(preorderState.messageMap[simpleProductId]);
                        preorderState.showPreorder();
                        isPreorder = true;
                    }
                    
                    this._E.availability.each(function(el) {
                        el = $(el);
                        el.addClassName('in-stock').removeClassName('out-of-stock');
                        if (!isPreorder && simpleProductId) {
                            el.select('span:not(.label)').invoke('update', Translator.translate('In Stock'));
                        }
                    });

                    this._E.cartBtn.btn.each(function (el, index) {
                        el = $(el);
                        el.disabled = false;
                        el.removeClassName('out-of-stock');
                        el.writeAttribute('onclick', this._E.cartBtn.onclick);
                        if (!isPreorder && simpleProductId) {
                            el.title = '' + Translator.translate(this._E.cartBtn.txt[index]);
                            el.select('span span').invoke('update', Translator.translate(this._E.cartBtn.txt[index]));
                        }
                    }.bind(this));
                }
            };
            
            Product.ConfigurableSwatches.prototype.checkStockStatus = function() {
                var inStock = true;
                var simpleProductId = 0;
                var checkOptions = arguments.length ? arguments[0] : this._E.activeConfigurableOptions;
                // Set out of stock if any selected item is not enabled
                checkOptions.each( function(selectedOpt) {
                    if (!selectedOpt._f.enabled) {
                        inStock = false;
                    } else if (1 == selectedOpt.products.length) {
                        simpleProductId = selectedOpt.products[0];
                    }
                });
                this.setStockStatus( inStock, simpleProductId );
            }
        }
    },

    getPreorderState : function () {
        if (typeof spConfig !== 'object') {
            console.log('Pre Order: spConfig not defined');
            return null;
        }

        if (typeof spConfig.getIdOfSelectedProduct !== 'function') {
            console.log('Pre Order: spConfig.getIdOfSelectedProduct not defined');
            return null;
        }

        var simpleProductId = spConfig.getIdOfSelectedProduct();
        
        /*if (Product.ConfigurableSwatches.prototype._E !== 'undefined') {
            var checkOptions = arguments.length ? arguments[0] : Product.ConfigurableSwatches.prototype._E.activeConfigurableOptions;
            checkOptions.each( function(selectedOpt) {
                if (1 == selectedOpt.products.length) {
                    var simpleProductId = selectedOpt.products[0];
                    throw $break;
                }
            });
        }*/
        
        if (simpleProductId) {
            this.cartLabel = this.cartLabelMap[simpleProductId];
            this.preorderNote.refresh(this.messageMap[simpleProductId]);
        }

        return simpleProductId && this.preorderMap[simpleProductId];
    },

    hidePreorder : function ($super) {
        this.preorderNote.disable();
        $super();
    },

    showPreorder : function ($super) {
        this.preorderNote.enable();
        $super();
    }
});

if (typeof Product != 'undefined' && typeof Product.Config != 'undefined') {
    Product.Config.prototype.getSelectedOptionsProductsUsingFrequencies = function () {
        var existingProducts = {};

        for(var i=this.settings.length-1;i>=0;i--)
        {
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if (selected && selected.config)
            {
                for(var iproducts=0;iproducts<selected.config.products.length;iproducts++)
                {
                    var usedAsKey = selected.config.products[iproducts]+"";
                    if(existingProducts[usedAsKey]==undefined)
                    {
                        existingProducts[usedAsKey]=1;
                    }
                    else
                    {
                        existingProducts[usedAsKey]=existingProducts[usedAsKey]+1;
                    }
                }
            }
        }

        return existingProducts;
    };

    Product.Config.prototype.getIdOfSelectedProduct = function()
    {
        var existingProducts = this.getSelectedOptionsProductsUsingFrequencies();

        for (var keyValue in existingProducts)
        {
            for ( var keyValueInner in existingProducts)
            {
                if(Number(existingProducts[keyValueInner])<Number(existingProducts[keyValue]))
                {
                    delete existingProducts[keyValueInner];
                }
            }
        }

        var sizeOfExistingProducts = 0;
        var currentSimpleProductId;
        for ( var keyValue in existingProducts)
        {
            currentSimpleProductId = keyValue;
            sizeOfExistingProducts++;
            if (sizeOfExistingProducts > 1) {
                break;
            }
        }

        return sizeOfExistingProducts == 1 ? currentSimpleProductId : null;
    };
}
