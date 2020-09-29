define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @export oroorder/js/app/views/shipping-tracking-collection-view
     * @extends oroui.app.views.base.View
     */
    const ShippingTrackingCollectionView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {},

        /**
         * @inheritDoc
         */
        constructor: function ShippingTrackingCollectionView(options) {
            ShippingTrackingCollectionView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options || {});

            this.$el
                .on('content:changed', _.bind(this.toggleTableVisibility, this))
                .on('content:remove', _.bind(this.toggleTableVisibility, this));
            this.$el.trigger('content:changed');
        },

        /**
         * Toggle Table visibility
         *
         * @param {jQuery.Event} e
         */
        toggleTableVisibility: function(e) {
            const table = this.$el.find('table');
            const elements = this.$el.find('table tr[data-content*="shippingTrackings"]');

            if (elements.length < 1 || (elements.length === 1 && e.type === 'content:remove')) {
                table.hide();
            } else {
                table.show();
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off('content:changed');
            this.$el.off('content:remove');

            ShippingTrackingCollectionView.__super__.dispose.call(this);
        }
    });

    return ShippingTrackingCollectionView;
});
