if (Prototype.BrowserFeatures.ElementExtensions) {
    // Fix incompatibilities between BootStrap and Prototype
    var disablePrototypeJS = function (method, pluginsToDisable) {
        var handler = function (event) {
            event.target[method] = undefined;
            setTimeout(function () {
                delete event.target[method];
            }, 0);
        };
        pluginsToDisable.each(function (plugin) {
            jQuery(window).on(method + '.bs.' + plugin, handler);
        });
    },
    pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover', 'tab'];
    disablePrototypeJS('show', pluginsToDisable);
    disablePrototypeJS('hide', pluginsToDisable);
}
