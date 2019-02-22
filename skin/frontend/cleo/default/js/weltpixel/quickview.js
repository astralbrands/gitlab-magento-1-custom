try {
    var weltpixel = weltpixel || {};

    weltpixel.quickview = weltpixel.quickview || {};
    weltpixel.quickview = {
        inIframe: false,
        iframeOpened: false,
        quickviewIframe: 'wpqv-iframe',
        init: function() {
            /*
            var items = $$('.category-products .item');

            if (items && (window.innerWidth > 480)) {
                items.each(function(elem) {
                    Event.observe(elem, 'touchend', function(e) {
                        e.cancelBubble = true;
                        items.invoke('removeClassName', 'item-hover');
                        elem.addClassName('item-hover');
                    });
                    Event.observe(elem, 'mouseover', function(e) {
                        elem.addClassName('item-hover');
                    });
                    Event.observe(elem, 'mouseout', function(e) {
                        elem.removeClassName('item-hover');
                    });
                });
                Event.observe(document, 'touchstart', function(e) {
                    items.invoke('removeClassName', 'item-hover');
                });
            }
            */
        },
        addToCart: function(url, data, addOptions)
        {
            data = data || {};

            weltpixel.lightbox.startLoader();
            new Ajax.Request(url, {
                method: 'post',
                parameters: data,
                onSuccess: function(transport, json) {
                    if (transport.responseJSON) {
                        if (typeof transport.responseJSON.blocks != 'undefined') {
                            for (block in transport.responseJSON.blocks) {
                                elementContent = transport.responseJSON.blocks[block].content;

                                if (typeof transport.responseJSON.blocks[block].class != 'undefined') {
                                    elementClass = transport.responseJSON.blocks[block].class;
                                    elem = $$('.' + transport.responseJSON.blocks[block].class)[0];
                                }

                                if (typeof transport.responseJSON.blocks[block].id != 'undefined') {
                                    elem = $(transport.responseJSON.blocks[block].id);
                                }

                                if (typeof elem != 'undefined') {
                                    elem.replace(elementContent);
                                }

                                if (elementClass == 'top-cart') {
                                    if (typeof truncateOptions == 'function') {
                                        truncateOptions();
                                    }
                                }
                            }
                        }

                        var responseDiv = new Element('div');
                        responseDiv.id = 'wpqv-addtocart-response';
                        responseDiv.update(transport.responseJSON.message);

                        weltpixel.lightbox.open(responseDiv);
                        var confirmationHide = parseInt(addOptions.confirmationHide);

                        if (confirmationHide) {
                            setTimeout(function(){weltpixel.lightbox.close()}, confirmationHide *1000);
                        }
                    }

                    addOptions.submittedForm = false;

                    weltpixel.lightbox.stopLoader();
                }
            });
        },
        showProductInfoInline: function(productUrl, elm)
        {
            ////////////////////////////////////////////////////////////////////

            function prepareContainer() {

                if ($('wpqv-inline')) {
                    $('wpqv-inline').remove();
                }

                var parentLI = elm.match('li') ? elm : elm.up('li'),
                    parentUL = parentLI.up('ul'),
                    liPerRow = Math.round(parentUL.getWidth() / parentLI.getWidth()),
                    currentRow = Math.floor(parentLI.previousSiblings().size() / liPerRow) + 1;
                    insertAfter = liPerRow * currentRow,
                    totalLI = parentLI.previousSiblings().size() + parentLI.nextSiblings().size() + 1;
                    if (parentLI.previousSiblings().size()) {
                        positionInRow = (parentLI.previousSiblings().size() % liPerRow) + 1;
                    } else {
                        positionInRow = (parentLI.up().previousSiblings().size() % liPerRow) + 1;
                    }

                if (insertAfter > totalLI) {
                    insertAfter = totalLI;
                };

                var inline = '<li id="wpqv-inline"></li>';

                $(parentUL.childElements()[insertAfter - 1]).insert({
                    after: inline
                });

                var container = '';
                container += '<div id="wpqv-container">';
                container += '    <div id="wpqv-cover"></div>';
                container += '    <div id="wpqv-loader"></div>';
                container += '    <div id="wpqv-pin"></div>';
                container += '    <div id="wpqv-close"><i class="icon-line-square-cross"></i></div>';
                container += '</div>';

                $('wpqv-inline').insert(container);

                $('wpqv-pin').setStyle({
                    left: (positionInRow - 1) * parentLI.getWidth() + 'px'
                });
            }

            prepareContainer();

            ////////////////////////////////////////////////////////////////////

            var iframeElement = new Element('iframe');
            iframeElement.id = this.quickviewIframe;
            iframeElement.frameBorder = 0;
            iframeElement.src = productUrl;

            ////////////////////////////////////////////////////////////////////

            $('wpqv-container').insert(iframeElement);

            ////////////////////////////////////////////////////////////////////

            $('wpqv-close').observe('click', function(event) {
                if ($('wpqv-inline')) {
                    $('wpqv-inline').remove();
                }
            });

            this.iframeOpened = true;
            /* Scroll to the opened elements*/
            jQuery('html,body').animate({
                scrollTop: jQuery("#wpqv-inline").offset().top -100
            });

            return false;
        },
        showProductInfoModal: function(productUrl)
        {
            var iframeElement = new Element('iframe');
            iframeElement.id = this.quickviewIframe;
            iframeElement.frameBorder = 0;
            iframeElement.src = productUrl;

            weltpixel.lightbox.startLoader();
            weltpixel.lightbox.open(iframeElement);

            this.iframeOpened = true;

            return false;
        },
        adjustIframe: function()
        {
            if (!this.iframeOpened) {
                return false;
            }

            var iframeWidth = $(this.quickviewIframe).contentWindow.document.body.scrollWidth,
                iframeHeight = $(this.quickviewIframe).contentWindow.document.body.scrollHeight;

            $(this.quickviewIframe).setStyle({
                width: iframeWidth + 'px',
                height: iframeHeight + 'px'
            });

            weltpixel.lightbox.adjustLightbox();
            weltpixel.lightbox.stopLoader();
        }
    };

    document.observe('dom:loaded', function() {
        weltpixel.lightbox.init();
        weltpixel.quickview.init();
    });

} catch (e) {
    console.log(e);
}
