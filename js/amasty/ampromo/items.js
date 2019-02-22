function ampromo_init()
{
    var itemsNode = $('ampromo-items');

    if (!itemsNode)
        return;

    $$('#ampromo-items form').each(function (form) {
        var validation = new Validation(form);
        form.validation = validation;

        var button = form.down('.button.add');
        if (button) {
            button.observe('click', function () {
                if (validation.validate()) {
                    $$('#ampromo-items button').each(function(element){
                        element.setAttribute('disabled', 'disabled');
                    });
                    this.up('form').submit();
                }
            });
        }

        form.select('.mark input[type=radio]').each(function (radio) {
            radio.observe('change', function () {
                var rule = this.up('[data-role="rule"]');
                rule.select('.mark input[type=radio]').each(function (radioToUncheck) {
                    radioToUncheck.checked = false;
                });

                rule.select('[data-role="item"]').each(function (item) {
                    item.removeClassName('checked');
                });

                this.checked = true;
            });
        });

        form.select('.mark input').each(function (input) {
            input.observe('change', function () {
                this.up('[data-role="item"]').toggleClassName('checked', this.checked);
            });
        });
    });

    $$('[data-role="rule"] [data-role="ampromo-rule-header"]').each(function (header) {
        header.observe('click', function () {
            this.up('[data-role="rule"]').toggleClassName('collapsed');
        });
    });

    $$('.bundle-option-select option,' +
    '.product-custom-option option').each(function(element){
            element.text = element.text.replace(/\s+\+.+$/, '');
    });

    if ($$('.bundle-option-select').length > 0) {
        if (!('bundle' in window)) {
            Object.extend(Product.Bundle.prototype, {
                initialize: function(){},
                changeSelection: function(){},
                reloadPrice: function(){}
                });
            window.bundle = new Product.Bundle({defaultValues: false});
        }
    }


    var overlay = $('ampromo-overlay');

    if (overlay) {
        var close = overlay.down('.close');
        if (close) {
            close.observe('click', function () {
                $('ampromo-overlay').fade();
            });
        }

        overlay.observe('click', function(event){
            if (event.target.id == 'ampromo-overlay')
                $('ampromo-overlay').fade();
        });

        if (itemsNode.hasClassName('amcarousel'))
        {
            window.ampromo_carousel = new Carousel(
                'ampromo-carousel-wrapper',
                $$('#ampromo-carousel-content .ampromo-slide'),
                $$('.ampromo-carousel-control'), {
                    visibleSlides: 3,
                    controlClassName: 'ampromo-carousel-control'
                }
            );

            ampromo_update_width();
            Event.observe(window, 'resize', ampromo_update_width);
        }

        var addAll = overlay.down('[data-role="add-all"]');

        if (addAll) {
            addAll.observe('click', ampromo_send_all);
        }
    }
}

function ampromo_send_all() {
    var data = [];

    var validationPassed = true;

    $$('#ampromo-items form').each(function (form) {
        var formData = form.serialize(true);
        if (formData.checked) {
            if (validationPassed &= form.validation.validate()) {
                data.push(formData);
            }
        }
    });

    if (validationPassed) {
        if (data.length > 0) {
            $$('#ampromo-overlay [data-role="add-all"]').each(function (button) {
                button.setAttribute('disabled', 'disabled');
            });

            var form = $('ampromo_metaform');
            form.down('[name=data]').value = JSON.stringify(data);
            form.submit();
        } else {
            alert('Please select any item');
        }
    }
}

function ampromo_update_width()
{
    var visibleSlides = $$('body')[0].clientWidth <= 820 ? 1 : 3;

    window.ampromo_carousel.options.visibleSlides = visibleSlides;
}


/**
 * Used to fix issues related to ajax reloads
 */

function ampromo_check_initialization(e)
{
    e.stop();
    var addBlock = $('ampromo-items-add');
    if (addBlock.readAttribute('data-initialized') === null)
    {
        addBlock.down('a').observe('click', ampromo_popup);
        ampromo_init();
        ampromo_popup();
        addBlock.setAttribute('data-initialized', '1');
    }
}

function ampromo_popup()
{
    var overlay = $('ampromo-overlay');

    if (overlay.visible())
        return;

    var items = $('ampromo-items');
    overlay.show();
    centerVertically(items);
    overlay.hide();

    overlay.appear();

    if (items.getStyle('position') == 'static' && $('amscheckout-main'))
        window.scroll(items.offsetLeft, items.offsetTop);
}

function centerVertically(element)
{
    var vpHeight = $(document).viewport.getHeight();
    var height = element.clientHeight;

    var avTop = (vpHeight / 2) - (height / 2);

    if (avTop <= 10)
        avTop = 10;

    element.style.top = avTop+ 'px';
}

function am_get_cookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

document.observe('dom:loaded', function(){
    if (+am_get_cookie('am_promo_notification')) {
        $$('.ampromo-notification').each(function (e) {
            e.show();
        })
    }
});

function ampromo_tooltip_show(evt){
    var img = Event.findElement(evt, 'img');
    var txt = img.alt;

    var data = $(img.id + '-data');
    var tooltip = $(img.id + '-tooltip');

    if (!tooltip && data) {
        tooltip           = document.createElement('div');
        tooltip.className = 'ampromo-tooltip';
        tooltip.id        = img.id + '-tooltip';
        tooltip.innerHTML = data.innerHTML;

        document.body.appendChild(tooltip);
    }

    var offset = Element.cumulativeOffset(img);
    tooltip.style.top  = (offset[1] + img.getHeight() + 5) + 'px';
    tooltip.style.left = (offset[0] ) + 'px';
    tooltip.show();
}

function ampromo_tooltip_hide(evt){
    var img = Event.findElement(evt, 'img');
    var tooltip = $(img.id + '-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}
