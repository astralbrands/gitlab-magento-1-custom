function send_alert_email(url, button) {
    var f = document.createElement('form');
    var productId = button.id.replace(/\D+/g, "");
    var block = button.up('.amxnotif-block');
    var notification = $('amxnotif_guest_email-' + productId);
    if (notification) {
        notification.addClassName("validate-email required-entry");
    }

    var gdpr = $('amxnotif-gdpr-' + productId);
    if (gdpr) {
        gdpr.addClassName("required-entry");
    }

    if (block) {
        if (block.select('.amxnotif-input-fields').length) {
            block = block.select('.amxnotif-input-fields').first();
        }

        block.childElements().each(function (child) {
            f.appendChild(child.cloneNode(true));
        });
    }

    var validator = new Validation(block);
    if (validator.validate()) {
        f.action = url;
        f.hide();
        $$('body')[0].appendChild(f);
        f.setAttribute("method", 'post');
        f.id = 'am_product_addtocart_form';
        f.submit();
        button.remove();
        return false;
    }

    if (notification) {
        notification.removeClassName("validate-email required-entry");
    }
    if (gdpr) {
        gdpr.removeClassName("required-entry");
    }

    return false;
}

function checkIt(evt, url, button) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13) {
        send_alert_email(url, button);
        return false;
    }
    return true;
}

var openPopup = null;

function showSubscribePopup(productId) {
    openPopup = $('subcribe-' + productId);
    openPopup.show();
    window.onclick = function(event) {
        if (event.target == openPopup) {
            closeSubscribePopup();
        }
    }
}

function closeSubscribePopup() {
    openPopup.hide();
    openPopup = null;
}

function disableButtonByGdpr(productId) {
    if ($('amxnotif-gdpr-' + productId).checked) {
        $('button-email-send-' + productId).disabled = false;
    } else {
        $('button-email-send-' + productId).disabled = true;
    }
}
