if (!Healthy) var Healthy = { };

Healthy.Functions = {
    currencyFormat: function(value, d, t) {
        if (!isNaN(parseFloat(value)) && isFinite(value)) {
            value = parseFloat(value);
            return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        } else {
            return 0;
        }
    },
};
