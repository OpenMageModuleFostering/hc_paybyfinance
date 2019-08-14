if (!Healthy) var Healthy = { };

Healthy.Slider = Class.create({
    // Class instantiation will call initialize. See
    // http://prototypejs.org/doc/latest/language/Class/create/
    initialize: function(widget, inputField, ruler, onchange, options) {
        var slider = this;

        slider.inputField = $(inputField);
        slider.max = slider.inputField.getAttribute('max');
        slider.widget = $(widget);
        slider.ruler = $(ruler);
        slider.pointer = slider.widget.down('.slider-pointer');
        slider.marker = slider.widget.down('.slider-marker');
        slider.onchange = onchange;
        slider.options = options || { };
        slider.touchstatus = "up";

        // Down.
        Event.observe(slider.widget, 'touchstart', slider.touchStart.bind(slider));
        Event.observe(slider.widget, 'mousedown', slider.touchStart.bind(slider));

        // Move.
        Event.observe(slider.widget, 'touchmove', slider.touchMove.bind(slider));
        Event.observe(window, 'mousemove', slider.touchMove.bind(slider));

        // Up.
        Event.observe(slider.widget, 'touchend', slider.touchUp.bind(slider));
        Event.observe(window, 'mouseup', slider.touchUp.bind(slider));

        // Window resize or zoom
        Event.observe(window, 'resize', slider.resize.bind(slider));

        // Redraw custom event
        Event.observe(slider.widget, 'pbf:redraw', slider.resize.bind(slider));

        // Manual change on input.
        Event.observe(slider.inputField, 'change', function(){
            slider.draw(slider);
            slider.onchange();
        });

        // This hack is required as hidden tabs has 0 width.
        $$('.toggle-content.tabs').each(function(item) {
            Event.observe(item, 'click', function() {
                slider.draw(slider);
            });
        });
        // Same for Enterprise.
        $$('.collateral-tabs .tab span').each(function(item) {
            Event.observe(item, 'click', function() {
                window.setTimeout(function() {
                    slider.draw(slider);
                }, 100);
            });
        });

        slider.draw(slider);
        this.onchange();
    },

    touchStart: function(e) {
        e.preventDefault();
        this.touchstatus = 'down';
    },

    touchMove: function(e) {
        slider = this;
        if (this.touchstatus == 'down') {
            e.preventDefault();
            slider.move(e);
        }
    },

    touchUp: function(e) {
        if (this.touchstatus == 'down') {
            e.preventDefault();
            this.touchstatus = 'up';
            this.onchange();
        }
    },

    change: function() {
        this.draw(this);
    },

    resize: function() {
        this.draw(this);
    },

    move: function (e) {
        var slider = this;
        var width = slider.widget.offsetWidth - 60;
        var x = e.pageX - slider.widget.cumulativeOffset().left;
        if (e && e.touches) x=e.touches[0].pageX - slider.widget.cumulativeOffset().left;
        var percent = x / (width+41) * slider.max;
        this.inputField.value = Math.round(percent);
        if (slider.options.secondInput) {
            $(slider.options.secondInput).value = percent;
        }
        slider.draw(slider);
    },

    getIeVersion: function() {
        if (!Prototype.Browser.IE) {
            return false;
        }
        if (window.ActiveXObject === undefined) return null;
        if (!document.querySelector) return 7;
        if (!document.addEventListener) return 8;
        if (!window.atob) return 9;
        if (!document.__proto__) return 10;
    },

    draw: function(slider) {
        var width = parseInt(this.widget.offsetWidth) ;
        if (width === 0 || isNaN(width)) {
            width = parseInt(this.widget.up().up().offsetWidth);
        }
        if (width === 0 || isNaN(width)) {
            width = parseInt(this.widget.up().up().up().offsetWidth);
        }
        if (width === 0 || isNaN(width)) {
            width = parseInt(this.widget.up().up().up().up().offsetWidth);
        }
        if (width === 0 || isNaN(width)) {
            width = parseInt(this.widget.up().up().up().up().up().offsetWidth);
        }
        if (width !== 0) {
            width = width - 34;
        }
        if (slider.max !== "0") {
            x = ((width / (parseInt(slider.max))) * slider.inputField.value) - 41;
            ie = slider.getIeVersion();

            shift = ((width) / (parseInt(slider.max)));

            if (!this.ruler.hasClassName('ruler-toggle')) {
                this.ruler.setStyle({width: (width + shift) + 'px'});
                if (!ie || ie >= 9) {
                    cellShift = this.ruler.offsetWidth / (parseInt(slider.max)+1) / 2;

                    this.ruler.setStyle({marginLeft: '-' + (cellShift-17) + 'px'});
                    this.ruler.setStyle({marginRight: '-' + (cellShift-17) + 'px'});
                }
            }

            if (slider.ruler.select('li').length !== 0) {
                slider.ruler.select('li').each(function(el) {
                    el.removeClassName('active');
                });

                if (slider.ruler.select('li.ruler-'+ slider.inputField.value).length !== 0) {
                    slider.ruler.select('li.ruler-'+ slider.inputField.value)[0].addClassName('active');
                }
            }

            this.pointer.setStyle({left: x + 'px'});
            this.marker.setStyle({width: (x + 41) + 'px'});
        } else {
            x = width / 2 - 41;
            this.pointer.setStyle({left: x + 'px'});
            this.marker.setStyle({width: (x + 41) + 'px'});
        }
    }

});
