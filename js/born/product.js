var PurCustomConfig = Class.create(Product.Config,{
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
		console.log()
        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
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
            if(i==0){
                //this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set default values - from config and overwrite them by url values
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

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

        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    }
});