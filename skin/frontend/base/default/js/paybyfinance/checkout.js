function pbfOnDOMLoad() {
    selector_data = window.pbf_selector_data;
    if (selector_data && selector_data.enabled) {
        if (document.getElementById('onestepcheckout-form')) {
            //if we're using onestepcheckout
            if ($('paybyfinance-info-p') == null) {
                $('billing_address').insert({
                    top: '<p id="paybyfinance-info-p" class="paybyfinance-info">If you are paying by finance then goods MUST be delivered to your billing address. Products can not be delivered outside the UK.</p>',
                });
            }
            hideCountries();
        } else {
            if ($('paybyfinance-info-p') == null) {
                $('co-billing-form').insert({
                    top: '<p id="paybyfinance-info-p" class="paybyfinance-info">If you are paying by finance then goods MUST be delivered to your billing address. Products can not be delivered outside the UK.</p>',
                });
            }
            $('billing:use_for_shipping_yes').checked = true;
            $('billing:use_for_shipping_no').up().hide();
            $('billing:use_for_shipping_no').disabled = true;
            hideCountries();
            $('shipping-new-address-form').up().hide();
            $('checkout-step-shipping').insert({
                top: '<p class="paybyfinance-info-shipping">It is not possible to select a different shipping address when the order is being paid by finance.</p>'
            });
            if ($('shipping-address-select') != null) {
                $('shipping-address-select').observe('change', function () {
                    alert('If you are paying by finance then goods MUST be delivered to your billing address. Products can not be delivered outside the UK.');
                });
            }
            $('billing:country_id').observe('focus', function () {
                var inputs = this.form.getElements();
                var idx = inputs.indexOf(this);
                inputs[idx + 1].focus(); // handles submit buttons
                inputs[idx + 1].select();
            });
        }
    }
}
document.observe('dom:loaded', function() {
    pbfOnDOMLoad();
});

function clearInputsInBilling(){
    var inputs = $('co-billing-form').getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
    }
}

function hideCountries() {
    $('billing:country_id').value = 'GB';
    if (window.billingRegionUpdater) {
        billingRegionUpdater.update();
    }
    var select2 = document.getElementById("billing:country_id");
    for (var i = 0; i < select2.length; i++) {
        if (select2.options[i].value != 'GB') {
            select2.options[i].style.display = "none";
        }
    }
    if(document.getElementById('onestepcheckout-form')) {
        $('billing:use_for_shipping_yes').checked = true;
        $('billing:use_for_shipping_yes').up().hide();
        $('shipping_address').hide();
    }
}

function showCountries() {
    if (window.billingRegionUpdater) {
        billingRegionUpdater.update();
    }
    var select2 = document.getElementById("billing:country_id");
    for (var i = 0; i < select2.length; i++) {
        select2.options[i].style.display = "block";
    }
    if(document.getElementById('onestepcheckout-form')) {
        $('billing:use_for_shipping_yes').up().show();
    }
}
