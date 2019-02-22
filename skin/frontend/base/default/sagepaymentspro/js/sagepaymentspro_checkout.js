toggleNewCard = function (action) {

    var adminFrms = $$("#order-billing_method_form [name='payment[method]']");
    var frontFrms = $$("#" + payment.form + " [name='payment[method]']");
    var msFrms = $$("#multishipping-billing-form [name='payment[method]']");

    if (adminFrms.length) {
        var frms = adminFrms;
    } else if (msFrms.length) {
        var frms = msFrms;
    } else {
        var frms = frontFrms;
    }

    var method = null;
    if (frms.length) {
        frms.each(
            function (el) {
            if (el.checked) {
                method = el.value;
            }
            }
        );
    }
    var frmSelector = 'div#payment_form_' + method;
    switch(parseInt(action)) {
        case 1 :
            var tokenInputs = $$(frmSelector + ' ul li.tokencard-radio input');
            if (parseInt(tokenInputs.length) === 0) {
                return;
            }
            if (adminFrms.length) {
                $$(frmSelector + ' ul.paymentsage select').each(
                    function (sl) {
                    sl.disabled = true;
                    }
                );
                $$(frmSelector + ' ul.paymentsage input').each(
                    function (sl) {
                    sl.disabled = true;
                    }
                );
            }

            $$(frmSelector + ' ul.paymentsage li', frmSelector + ' ul.paymentsage').invoke('hide');
            tokenInputs.each(
                function (radiob) {
                radiob.disabled = false;
                }
            );
            $$(frmSelector + ' ul li.tokencard-radio', frmSelector + ' a.addnew').invoke('show');

            break;
        case 2 :
            $$(frmSelector + ' li', frmSelector + ' ul').invoke('show');
            $$(frmSelector + ' ul li.tokencard-radio input').each(
                function (radiob) {
                //radiob.disabled = 'disabled';
                radiob.disabled = true;
                }
            );

            if (adminFrms.length) {
                $$(frmSelector + ' ul.paymentsage select').each(
                    function (sl) {
                    sl.disabled = false;
                    }
                );
                $$(frmSelector + ' ul.paymentsage input').each(
                    function (sl) {
                    sl.disabled = false;
                    }
                );
            }

            $$(frmSelector + ' ul li.tokencard-radio', frmSelector + ' a.addnew').invoke('hide');
            break;
        case 3:
            break;
    }

}
tokenRadioCheck = function(radioID, cvv){
    try{
        $(radioID).checked = true;
    }catch(noex){}

    var adminFrms = $$("#order-billing_method_form [name='payment[method]']");
    if(adminFrms.length){
        var frmSelector = 'div#payment_form_sagepaymentspro';
        $$(frmSelector + ' ul.paymentsage select').each(
            function(sl){
            sl.disabled = true;
            }
        );
        $$(frmSelector + ' ul.paymentsage input').each(
            function(sl){
            sl.disabled = true;
            }
        );
        $$('input.tokencvv').each(
            function(sl){
            if(sl.id != cvv.id){
                sl.disabled = true;
            }
            }
        );
    }
}

switchToken = function (radio) {
    $$('div.tokencvv').invoke('hide');
    $$('input.tokencvv').each(
        function (inp) {
        inp.disabled = 'disabled';
        }
    )

    if ($('serversecure')) {
        $('serversecure').hide();
    }

    var divcont = radio.next('div');
    if ((typeof divcont) != 'undefined') {
        divcont.down().next('input').removeAttribute('disabled');
        divcont.show();
    }
}
removeCard = function(elem){

    var oncheckout = elem.hasClassName('oncheckout');

    new Ajax.Request(
        elem.href, {
        method: 'get',
        onSuccess: function(transport) {
            try{
                var rsp = transport.responseText.evalJSON();

                if(rsp.st != 'ok'){
                    //var ng = new k.Growler({location:"tc"});
                    //			ng.warn(rsp.text, {life:10});
                    alert(rsp.text);
                }else{
                    if(false === oncheckout){
                        elem.up().up().fade(
                            {
                            afterFinish:function(){
                            elem.up().up().remove();
                            updateEvenOdd();
                            }
                            }
                        );
                    }else{
                        elem.up().fade(
                            {
                            afterFinish:function(){
                            elem.up().remove();
                            }
                            }
                        );
                    }
                }

                if(!oncheckout){
                    $('sagepaymentsproTokenCardLoading').hide();
                }
            }catch(er){
                alert(er);
            }
        },
        onLoading: function(){
            if(!oncheckout){
                if($('iframeRegCard')){
                    $('iframeRegCard').remove();
                }else if($('frmRegCard')){
                    $('frmRegCard').remove();
                }
                $('sagepaymentsproTokenCardLoading').show();
            }

        }
        }
    )

}
if(typeof EbizmartsSagePaymentsPro == 'undefined') {
    var EbizmartsSagePaymentsPro = {};
}
EbizmartsSagePaymentsPro.Checkout = Class.create();
EbizmartsSagePaymentsPro.Checkout.prototype = {

    initialize: function(config) {
        this.config             = config;
        this.code               = '';
        this.oldUrl             = '';
        this.customckout        = null;

    },
    getConfig: function(instance){
        return (this.config[instance] != 'undefined' ? this.config[instance] : false);
    },
    getCurrentCheckoutStep: function(){
        return this.getConfig('checkout').accordion.currentSection;
    },
    getPaymentMethod: function() {

        var form = null;

        if($('multishipping-billing-form')){
            form = $('multishipping-billing-form');
        }else if(this.getConfig('osc')){
            form = this.getConfig('oscFrm');
        }else if((typeof this.getConfig('payment')) != 'undefined'){
            form = $(this.getConfig('payment').form);
        }

        if(form === null){
            return this.code;
        }

        var checkedPayment = null

        form.getInputs('radio', 'payment[method]').each(
            function(el){
            if(el.checked){
                checkedPayment = el.value;
                throw $break;
            }
            }
        );

        if(checkedPayment != null){
            return checkedPayment;
        }

        return this.code;
    },
    reviewSave: function(transport) {
    }
}

try{
    Event.observe(
        window,"load", function(){
        var msCont = $('sagepayments_payment_method');
        if(!msCont && ((typeof window.review) != 'undefined')) {
            var Sage = new EbizmartsSagePaymentsPro.Checkout(
                {
                    'checkout':             window.checkout,
                    'review':               window.review,
                    'payment':              window.payment,
                    'billing':              window.billing,
                    'accordion':            window.accordion
                }
            );

        }
        else if(msCont && msCont.getValue() == 'sagepaymentsproserver' ) {
            var Sage = new EbizmartsSagePaymentsPro.Checkout(
                {
                    'msform': $$('div.multiple-checkout')[0].down(2)
                }
            );
        }

        if(parseInt(SageConfig.getConfig('global','valid')) === 0){
                new PeriodicalExecuter(
                    function(){
                    alert(SageConfig.getConfig('global','not_valid_message'));
                    }, 10
                );
        }
        $(document.body).insert(
            new Element(
                'a', {
                'id': 'sagepaymentsserver-dummy-link',
                'href': '#',
                'style':'display:none'
                }
            ).update('&nbsp;')
        );
        }
    )
}catch(er){
    sageLogError(er);
}
