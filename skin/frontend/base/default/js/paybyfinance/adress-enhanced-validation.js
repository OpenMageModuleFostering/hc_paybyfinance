if(Validation) {
    Validation.addAllThese([
        ['validate-one-required-by-reqGroup', 'Please select one of the options.', function (v,elm) {
            var groupName = elm.getAttribute('reqGroup');
            var inputs = $$('input[reqGroup="' + groupName.replace(/([\\"])/g, '\\$1') + '"]');
            var error = 1;
            for(var i=0;i<inputs.length;i++) {
                if((inputs[i].type == 'checkbox' || inputs[i].type == 'radio') && inputs[i].checked == true) {
                    error = 0;
                }

                if(inputs[i].type == 'text' && inputs[i].value !== "") {
                    error = 0;
                }

                if(Validation.isOnChange && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) {
                    Validation.reset(inputs[i]);
                }
            }

            if( error == 0 ) {
                return true;
            } else {
                return false;
            }
        }],
        [ ]
    ])
}
