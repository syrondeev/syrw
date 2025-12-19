/**
 * SYRW Elementor Widgets - Global Scripts
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    var SYRWWidgets = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(window).on('elementor/frontend/init', this.onElementorInit);
        },

        onElementorInit: function() {
            // Initialize widgets when Elementor frontend is ready
            console.log('SYRW Widgets Loaded');
        }
    };

    $(document).ready(function() {
        SYRWWidgets.init();
    });

})(jQuery);
