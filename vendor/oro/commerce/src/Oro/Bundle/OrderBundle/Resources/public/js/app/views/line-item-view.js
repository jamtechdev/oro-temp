define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const ProductUnitComponent = require('oroproduct/js/app/components/product-unit-component');
    const LineItemProductView = require('oroproduct/js/app/views/line-item-product-view');

    /**
     * @export oroorder/js/app/views/line-item-view
     * @extends oroui.app.views.base.View
     * @class oroorder.app.views.LineItemView
     */
    const LineItemView = LineItemProductView.extend({
        /**
         * @property {Object}
         */
        options: {
            selectors: {
                productSelector: '.order-line-item-type-product [data-name="field__product"]',
                quantitySelector: '.order-line-item-quantity input',
                unitSelector: '.order-line-item-quantity select',
                productSku: '.order-line-item-sku .order-line-item-type-product',
                productType: '.order-line-item-type-product',
                freeFormType: '.order-line-item-type-free-form'
            },
            freeFormUnits: null
        },

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {jQuery}
         */
        $fields: null,

        /**
         * @property {Object}
         */
        fieldsByName: null,

        /**
         * @inheritDoc
         */
        constructor: function LineItemView(options) {
            LineItemView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});

            LineItemView.__super__.initialize.call(this, options);

            this.delegate('click', '.removeLineItem', this.removeRow);
            this.initializeUnitLoader();
        },

        /**
         * Initialize unit loader component
         */
        initializeUnitLoader: function() {
            const defaultOptions = {
                _sourceElement: this.$el,
                productSelector: this.options.selectors.productSelector,
                quantitySelector: this.options.selectors.quantitySelector,
                unitSelector: this.options.selectors.unitSelector,
                loadingMaskEnabled: false,
                dropQuantityOnLoad: false,
                defaultValues: this.options.freeFormUnits,
                model: this.model
            };

            this.subview('productUnitComponent', new ProductUnitComponent(_.extend({}, defaultOptions)));
        },

        /**
         * Doing something after loading child components
         */
        handleLayoutInit: function(options) {
            this.$form = this.$el.closest('form');
            this.$fields = this.$el.find(':input[name]');

            this.fieldsByName = {};
            this.$fields.each(_.bind(function(i, field) {
                this.fieldsByName[this.formFieldName(field)] = $(field);
            }, this));

            this.initProduct();

            this.fieldsByName.currency = this.$form
                .find(':input[data-ftid="' + this.$form.attr('name') + '_currency"]');

            this.subtotalFields([
                this.fieldsByName.quantity,
                this.fieldsByName.productUnit,
                this.fieldsByName.priceValue,
                this.fieldsByName.priceType
            ]);

            this.initTypeSwitcher();

            LineItemView.__super__.handleLayoutInit.call(this, options);
        },

        initTypeSwitcher: function() {
            const $product = this.$el.find('div' + this.options.selectors.productType);
            const $freeForm = this.$el.find('div' + this.options.selectors.freeFormType);

            const showFreeFormType = function() {
                $product.hide();
                $freeForm.show();
            };

            const showProductType = function() {
                $freeForm.hide();
                $product.show();
            };

            $freeForm.find('a' + this.options.selectors.productType).click(_.bind(function() {
                showProductType();
                $freeForm.find(':input').val('').change();
            }, this));

            $product.find('a' + this.options.selectors.freeFormType).click(_.bind(function() {
                showFreeFormType();
                this.fieldsByName.product.inputWidget('val', '');
                this.fieldsByName.product.change();
            }, this));

            if (this.fieldsByName.freeFormProduct.val() !== '') {
                showFreeFormType();
            } else {
                showProductType();
            }
        },

        /**
         * @param {Object} field
         * @returns {String}
         */
        formFieldName: function(field) {
            let name = '';
            const nameParts = field.name.replace(/.*\[[0-9]+\]/, '').replace(/[\[\]]/g, '_').split('_');
            let namePart;

            for (let i = 0, iMax = nameParts.length; i < iMax; i++) {
                namePart = nameParts[i];
                if (!namePart.length) {
                    continue;
                }
                if (name.length === 0) {
                    name += namePart;
                } else {
                    name += namePart[0].toUpperCase() + namePart.substr(1);
                }
            }
            return name;
        },

        /**
         * @param {jQuery|Array} $fields
         */
        subtotalFields: function($fields) {
            _.each($fields, function(field) {
                $(field).attr('data-entry-point-trigger', true);
            });

            mediator.trigger('entry-point:order:init');
        },

        removeRow: function() {
            this.$el.trigger('content:remove');
            this.remove();

            mediator.trigger('entry-point:order:trigger');
        },

        resetData: function() {
            this.model.set('quantity', 1);

            if (this.fieldsByName.hasOwnProperty('priceValue')) {
                this.fieldsByName.priceValue.val(null).addClass('matched-price');
            }
        },

        initProduct: function() {
            if (this.fieldsByName.product) {
                this.fieldsByName.product.change(_.bind(function() {
                    this.resetData();

                    const data = this.fieldsByName.product.inputWidget('data') || {};
                    this.$el.find(this.options.selectors.productSku).text(data.sku || null);
                }, this));
            }
        }
    });

    return LineItemView;
});
