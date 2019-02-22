try {
    var weltpixel = weltpixel || {};

    weltpixel.lightbox = weltpixel.lightbox || {};
    weltpixel.lightbox = {
        initialized: false,
        loaderStarted: false,
        opened: false,
        overlayDivId: 'lightbox-overlay',
        lightboxDivId: 'lightbox-content',
        loaderDivId: 'lightbox-loader',
        closeDivId: 'lightbox-close',
        closeCallback: null,
        init: function(closeCallback) {
            if (this.initialized) {
                return;
            }

            this.closeCallback = closeCallback;

            overlayDiv = new Element('div');
            overlayDiv.id = this.overlayDivId;
            $$('body')[0].insert(overlayDiv);

            lightboxDiv = new Element('div');
            lightboxDiv.id = this.lightboxDivId;
            $$('body')[0].insert(lightboxDiv);

            loaderDiv = new Element('div');
            loaderDiv.id = this.loaderDivId;
            $$('body')[0].insert(loaderDiv);

            $(this.overlayDivId).observe('click', this.close.bind(this));
            window.onresize = this.adjust.bind(this);
            window.onkeypress = this.checkKey.bind(this);

            this.initialized = true;
        },
        startLoader: function() {
            if (!this.opened) {
                $(this.overlayDivId).setStyle({display: 'block'});
            }
            $(this.loaderDivId).setStyle({display: 'block'});

            this.loaderStarted = true;

            this.adjustLoader();
        },
        stopLoader: function() {
            if (!this.loaderStarted) {
                return;
            }
            if (!this.opened) {
                $(this.overlayDivId).hide();
            }

            $(this.closeDivId).setStyle({display: 'block'});

            $(this.loaderDivId).hide();
            this.loaderStarted = false;
        },
        open: function(content) {
            if (this.opened) {
                this.close();
                this.opened = false;
            }

            $(this.lightboxDivId).update(content);

            $(this.lightboxDivId).insert('<div id="' + this.closeDivId + '">&times;</div>');
            $(this.closeDivId).observe('click', this.forceClose.bind(this));

            $(this.overlayDivId).setStyle({display: 'block'});
            $(this.lightboxDivId).setStyle({display: 'block'});

            this.opened = true;

            this.adjustLightbox();
        },
        forceClose: function() {
            try {
                this.closeCallback();
            } catch(e) {}
            this.close();
        },
        close: function() {
            $(this.lightboxDivId).update('');

            $(this.loaderDivId).hide();
            $(this.overlayDivId).hide();
            $(this.lightboxDivId).hide();

            this.loaderStarted = false;
            this.opened = false;
        },
        checkKey: function(e) {
            if (!this.opened) {
                return;
            }

            code = e.keyCode ? e.keyCode : e.which;
            if (code == 27) {
                this.close();
            }
        },
        adjust: function() {
            this.adjustLightbox();
            this.adjustLoader();
        },
        adjustLightbox: function() {
            if (!this.opened) {
                return;
            }

            lightboxHeight = $(this.lightboxDivId).getHeight();
            lightboxWidth = $(this.lightboxDivId).getWidth();

            var leftPos = 0;
            if (document.body.offsetWidth > lightboxWidth) {
                leftPos += (document.body.offsetWidth - lightboxWidth) / 2;
            }

            var topPos = window.pageYOffset;
            if (window.innerHeight > lightboxHeight) {
                topPos += (window.innerHeight - lightboxHeight) / 2;
            }

            $(this.lightboxDivId).setStyle({
                left: leftPos + 'px',
                top: topPos + 'px'
            });
        },
        adjustLoader: function() {
            if (!this.loaderStarted) {
                return;
            }

            loaderHeight = $(this.loaderDivId).getHeight();
            loaderWidth = $(this.loaderDivId).getWidth();

            var leftPos = 0;
            if (document.body.offsetWidth > loaderWidth) {
                leftPos += (document.body.offsetWidth - loaderWidth) / 2;
            }

            var topPos = 0;
            if (window.innerHeight > loaderHeight) {
                topPos += (window.innerHeight - loaderHeight) / 2;
            }

            $(this.loaderDivId).setStyle({
                left: leftPos + 'px',
                top: topPos + 'px'
            });
        }
    };
} catch (e) {
    console.log(e);
}
