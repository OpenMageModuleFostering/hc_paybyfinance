var PaybyfinanceConfigEdit = Class.create();
PaybyfinanceConfigEdit.prototype = {
    initialize: function() {
        this.section = $('hc_paybyfinance_account');
        this.modeField = $('hc_paybyfinance_account_connectionmode');
        if (this.modeField.value == 'custom') {
            // Display the custom fields
            this.show();
        } else {
            this.hide();
        }

        this.modeField.observe('change', this.modeChange.bind(this));
    },

    hide: function(animate) {
        animate = animate || false;
        if (animate) {
            Effect.Fade('row_hc_paybyfinance_account_connection_post', {duration: 1});
            Effect.Fade('row_hc_paybyfinance_account_connection_notify', {duration: 1});
        } else {
            $('row_hc_paybyfinance_account_connection_post').hide();
            $('row_hc_paybyfinance_account_connection_notify').hide();
        }
    },

    show: function(animate) {
        animate = animate || false;
        if (animate) {
            Effect.Appear('row_hc_paybyfinance_account_connection_post', {duration: 1});
            Effect.Appear('row_hc_paybyfinance_account_connection_notify', {duration: 1});
        } else {
            $('row_hc_paybyfinance_account_connection_post').show();
            $('row_hc_paybyfinance_account_connection_notify').show();
        }
    },

    modeChange: function(event) {
        mode = this.modeField.value;
        console.log(mode);
        if (mode == 'custom') {
            this.show(true);
        } else {
            this.hide(true);
        }

    }
};
Event.observe(window, 'load',  function() {
    if ($('hc_paybyfinance_account_connectionmode')) {
        paybyfinanceConfigEdit = new PaybyfinanceConfigEdit();
    }
});
