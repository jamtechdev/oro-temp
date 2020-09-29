define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const ProductsPricesComponent = require('oropricing/js/app/components/products-prices-component');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export orosale/js/app/views/line-items-view
     * @extends oroui.app.views.base.View
     * @class orosale.app.views.LineItemsView
     */
    const LineItemsView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            tierPrices: null,
            tierPricesRoute: '',
            currency: null,
            customer: null
        },

        /**
         * @inheritDoc
         */
        constructor: function LineItemsView(options) {
            LineItemsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});

            this.subview('productsPricesComponent', new ProductsPricesComponent({
                tierPrices: this.options.tierPrices,
                tierPricesRoute: this.options.tierPricesRoute,
                currency: this.options.currency,
                customer: this.options.customer
            }));

            this.initLayout().done(_.bind(this.handleLayoutInit, this));
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function() {
            this.$el.find('.add-lineitem').mousedown(function() {
                $(this).click();
            });
        }
    });

    return LineItemsView;
});
