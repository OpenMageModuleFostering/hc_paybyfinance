var PaybyfinanceService = Class.create();
PaybyfinanceService.prototype = {
    initialize: function() {
        this.form = $('edit_form');
        this.aprField = $('apr');
        this.rpmField = $('rpm');
        this.rpmField.readOnly = true;

        this.aprField.observe('change', this.aprChange.bind(this));
        this.aprField.observe('blur', this.aprChange.bind(this));
    },

    aprChange: function(event) {
        apr = this.aprField.value;

        rpm = (Math.pow((parseFloat(apr)/100 + 1), (1 / 12)) - 1) * 100;
        rpm = Math.round(rpm * 1000) / 1000;

        this.rpmField.value = rpm;
    }
};
Event.observe(window, 'load',  function() {
    paybyfinanceService = new PaybyfinanceService();
});
