try {
    if (typeof weltpixel == 'undefined') {
        weltpixel = {};
    }

    if (typeof weltpixel.newsletterpopup == 'undefined') {
        weltpixel.newsletterpopup = {
            opened: false,
            overlayDivId: 'lightbox-overlay',
            lightboxDivId: 'lightbox-content',
            closeDivId:    'lightbox-close-newsletter',
            closeCallback: null,
            closeOnOverlayAlso : false,
            init: function(closeCallback, closeOnOverlayAlso) {
                this.closeCallback = closeCallback;
                this.closeOnOverlayAlso = closeOnOverlayAlso;
                overlayDiv = new Element('div');
                overlayDiv.id = this.overlayDivId;
                $$('body')[0].insert(overlayDiv);

                lightboxDiv = new Element('div');
                lightboxDiv.id = this.lightboxDivId;
                $$('body')[0].insert(lightboxDiv);

                if (this.closeOnOverlayAlso) {
                    try {
                        this.closeCallback();
                    } catch(e) {}
                } 
                $(this.overlayDivId).observe('click', this.close.bind(this));
                window.onresize = this.adjustLightbox.bind(this);
            },
            open: function(content) {
                if (this.opened) {
                    this.close();
                    this.opened = false;
                }
                
                $(this.lightboxDivId).update(content);
                $(this.lightboxDivId).insert("<div id='"+this.closeDivId+"'>X</div>");
                $(this.closeDivId).observe('click', this.forceClose.bind(this));

                $(content).setStyle({display: 'block'});
                $(this.overlayDivId).setStyle({display: 'block'});
                $(this.lightboxDivId).setStyle({display: 'block'});

                this.opened = true;

                this.adjustLightbox();
            },
            close: function() {              
                $(this.lightboxDivId).update('');

                $(this.overlayDivId).hide();
                $(this.lightboxDivId).hide();
            },
            forceClose: function() {
                 try {
                    this.closeCallback();
                } catch(e) {}
                this.close();
            },
            adjustLightbox: function() {
                if (!this.opened) {
                    return;
                }

                lightboxHeight = $(this.lightboxDivId).getHeight();
                lightboxWidth = $(this.lightboxDivId).getWidth();

                leftPos = 0;
                if (document.body.offsetWidth > lightboxWidth) {
                    leftPos += (document.body.offsetWidth - lightboxWidth)/2;
                }
                topPos = window.pageYOffset;
                if (window.innerHeight > lightboxHeight) {
                    topPos += (window.innerHeight - lightboxHeight)/2;
                }

                $(this.lightboxDivId).setStyle({
                    left: leftPos + 'px',
                    top: topPos + 'px'
                });
            }
        };
    }
} catch (e) {
    console.log(e)
}
