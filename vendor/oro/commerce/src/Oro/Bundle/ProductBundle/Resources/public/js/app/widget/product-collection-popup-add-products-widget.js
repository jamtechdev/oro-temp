define(function(require) {
    'use strict';

    const DialogWidget = require('oro/dialog-widget');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');

    /**
     * This widget is responsible for triggering appropriate event given in options and passing array of products
     * selected in grid to this event.
     */
    const ProductCollectionPopupAddProductsWidget = DialogWidget.extend({
        /**
         * @property {Array}
         */
        requiredOptions: ['gridName', 'hiddenProductsSelector'],

        /**
         * @inheritDoc
         */
        constructor: function ProductCollectionPopupAddProductsWidget(options) {
            ProductCollectionPopupAddProductsWidget.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            ProductCollectionPopupAddProductsWidget.__super__.initialize.call(this, options);

            this._checkOptions();

            this.getAction('addProducts', 'adopted', _.bind(function(actionElement) {
                actionElement.on('click', _.bind(this._triggerEvent, this));
            }, this));

            mediator.on('product-collection-add-to-excluded', this._closeDialogWidget, this);
            mediator.on('product-collection-add-to-included', this._closeDialogWidget, this);
        },

        /**
         * @private
         */
        _checkOptions: function() {
            const requiredMissed = this.requiredOptions.filter(_.bind(function(option) {
                return _.isUndefined(this.options[option]);
            }, this));
            if (requiredMissed.length) {
                throw new TypeError('Missing required option(s): ' + requiredMissed.join(', '));
            }
        },

        /**
         * @private
         */
        _triggerEvent: function() {
            mediator.trigger('get-selected-products-mass-action-run:' + this.options.gridName);
        },

        /**
         * @private
         */
        _closeDialogWidget: function() {
            this.remove();
        },

        /**
         * @private
         */
        _getWidgetData: function() {
            const widgetData = ProductCollectionPopupAddProductsWidget.__super__._getWidgetData.call(this);
            const val = $(this.options.hiddenProductsSelector).val();

            if (val) {
                widgetData.hiddenProducts = val;
            }

            return widgetData;
        },

        /**
         * @inheritDoc
         */
        loadContent: function(...args) {
            if (args.length) {
                ProductCollectionPopupAddProductsWidget.__super__.loadContent.apply(this, args);
            } else {
                const oldFirstRun = this.firstRun;
                this.firstRun = false;
                ProductCollectionPopupAddProductsWidget.__super__.loadContent.call(this, undefined, 'post');
                this.firstRun = oldFirstRun;
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off(null, null, this);

            ProductCollectionPopupAddProductsWidget.__super__.dispose.call(this);
        }
    });

    return ProductCollectionPopupAddProductsWidget;
});
