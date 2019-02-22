var CustomConfig = Class.create(Product.Config, {
            initialize: function(config, containerId){
                this.config     = config;
                
                this.taxConfig  = this.config.taxConfig;
                if (containerId) {
                    this.settings   = $$('#' + containerId + ' ' + '.super-attribute-select');
                } else {
                    this.settings   = $$('.super-attribute-select');
                }
                this.state      = new Hash();
                this.priceTemplate = new Template(this.config.template);
                this.prices     = config.prices;

                //delete repeatable elements
                for(var a = 0; a < this.settings.length; a++) {
                    if (this.settings[a].nodeName.toLowerCase() === 'div') {
                        this.settings.splice(a, 1);
                    }
                    if (jQuery(this.settings[a]).attr('prod-init')) {
                        this.settings.splice(a, 1);
                        a--;
                    }
                    else {
                        jQuery(this.settings[a]).attr('prod-init', true);
                    }
                }

                // Set default values from config
                if (config.defaultValues) {
                    this.values = config.defaultValues;
                }

                // Overwrite defaults by url
                var separatorIndex = window.location.href.indexOf('#');
                if (separatorIndex != -1) {
                    var paramsStr = window.location.href.substr(separatorIndex+1);
                    var urlValues = paramsStr.toQueryParams();
                    if (!this.values) {
                        this.values = {};
                    }
                    for (var i in urlValues) {
                    this.values[i] = urlValues[i];
                }
            }

            // Overwrite defaults by inputs values if needed
            if (config.inputsInitialized) {
                this.values = {};
                this.settings.each(function(element) {
                    if (element.value) {
                        var attributeId = element.id.replace(/[a-z]*/, '');
                        this.values[attributeId] = element.value;
                    }
                }.bind(this));
            }

            // Put events to check select reloads
            this.settings.each(function(element){
                jQuery(element).on('change', this.configure.bind(this));
                Event.observe(element, 'change', this.configure.bind(this));
            }.bind(this));

            // fill state
            this.settings.each(function(element){
                var attributeId = element.id.replace(/[a-z]*/, '');
                if(attributeId && this.config.attributes[attributeId]) {
                    element.config = this.config.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.state[attributeId] = false;
                }
            }.bind(this))

            // Init settings dropdown
            var childSettings = [];
            for(var i=this.settings.length-1;i>=0;i--){
                var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
                var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
                if (i == 0){
                    this.fillSelect(this.settings[i]);
                } else {
                    this.settings[i].disabled = true;
                }
                $(this.settings[i]).childSettings = childSettings.clone();
                $(this.settings[i]).prevSetting   = prevSetting;
                $(this.settings[i]).nextSetting   = nextSetting;
                childSettings.push(this.settings[i]);
            }

            // Set values to inputs
            this.configureForValues();
            document.observe("dom:loaded", this.configureForValues.bind(this));
            this.configureObservers = [];
                this.loadOptions();
            },
            loadOptions: function(){
                this.settings.each(function(element){
                element.disabled = false;
                element.options[0] = new Option(this.config.chooseText, '');
                var attributeId = element.id.replace(/[a-z]*/, '');
                var options = this.getAttributeOptions(attributeId);
                if(options) {
                var index = 1;
                    for(var i=0;i<options.length;i++){
                    options[i].allowedProducts = options[i].products.clone();
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    if (typeof options[i].price != 'undefined') {
                        element.options[index].setAttribute('price', options[i].price);
                    }
                    element.options[index].setAttribute('data-label', options[i].label.toLowerCase());
                    element.options[index].config = options[i];
                    index++;
                }
            }
                this.reloadOptionLabels(element);
            }.bind(this));
            },
            configureElement : function(element) {
                this.reloadOptionLabels(element);
                if(element.value){
                    this.state[element.config.id] = element.value;
                    if(element.nextSetting){
                        element.nextSetting.disabled = false;
                        this.fillSelect(element.nextSetting);
                        this.resetChildren(element.nextSetting);
                    }
                }
                else {
                    this.resetChildren(element);
                }
                this.reloadPrice();
                jQuery(element).trigger('refresh');
            }
        });