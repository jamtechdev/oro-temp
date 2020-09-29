define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const TaxFormatter = require('orotax/js/formatter/tax');

    /**
     * @export orotax/js/app/components/order-line-item-item-component
     * @extends oroui.app.components.base.Component
     * @class orotax.app.components.OrderLineItemItemComponent
     */
    const OrderLineItemItemComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                templateSelector: '#tax-item',
                lineItemDataAttr: 'data-tax-item',
                valueContainer: '[data-value-container]'
            },
            type: null,
            value: null,
            currencyProp: 'currency'
        },

        /**
         * @property {Object}
         */
        template: null,

        /**
         * @property {jQuery.Element}
         */
        $valueContainer: null,

        /**
         * @inheritDoc
         */
        constructor: function OrderLineItemItemComponent(options) {
            OrderLineItemItemComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            const attr = this.getAttribute();
            this.options._sourceElement.attr(attr, $('[' + attr + ']').length);

            this.template = _.template($(this.getTemplateName()).html());

            this.$valueContainer = this.options._sourceElement.find(this.options.selectors.valueContainer);

            mediator.on('entry-point:order:load', this.setItemValue, this);
            mediator.on('entry-point:order:load:before', this.initializeAttribute, this);
        },

        initializeAttribute: function() {
            const attr = this.getAttribute();
            $('[' + attr + ']').each(function(index) {
                $(this).attr(attr, index);
            });
        },

        getAttribute: function() {
            return [this.options.selectors.lineItemDataAttr, this.options.type, this.options.value].join('-');
        },

        getTemplateName: function() {
            return [this.options.selectors.templateSelector, this.options.type, this.options.value].join('-');
        },

        /**
         * @param {Object} response
         */
        setItemValue: function(response) {
            const result = _.defaults(response, {taxItems: {}});
            const itemId = this.options._sourceElement.attr(this.getAttribute());

            if (!_.has(result.taxItems, itemId)) {
                return;
            }

            const itemData = _.defaults(response.taxItems[itemId], {});

            if (!_.has(itemData, this.options.type)) {
                return;
            }

            if (!_.has(itemData[this.options.type], this.options.value)) {
                return;
            }

            const value = TaxFormatter.formatElement(
                itemData[this.options.type][this.options.value],
                itemData[this.options.type][this.options.currencyProp]
            );

            this.$valueContainer.html(this.template({value: value}));
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('entry-point:order:load', this.setItemValue, this);
            mediator.off('entry-point:order:load:before', this.initializeAttribute, this);

            OrderLineItemItemComponent.__super__.dispose.call(this);
        }
    });

    return OrderLineItemItemComponent;
});
