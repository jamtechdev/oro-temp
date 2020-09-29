import BaseTypeBuilder from 'orocms/js/app/grapesjs/type-builders/base-type-builder';
import tableResponsiveTemplate from 'tpl-loader!orocms/templates/grapesjs-table-responsive.html';

/**
 * Create responsive table component type for builder
 */
const TableResponsiveTypeBuilder = BaseTypeBuilder.extend({
    componentType: 'table-responsive',

    button: {
        label: 'Table',
        category: 'Basic',
        attributes: {
            'class': 'fa fa-table'
        }
    },

    template: tableResponsiveTemplate,

    /**
     * @inheritDoc
     */
    constructor: function TableResponsiveTypeBuilder(options) {
        TableResponsiveTypeBuilder.__super__.constructor.call(this, options);
    },

    modelMixin: {
        defaults: {
            tagName: 'div',
            draggable: ['div'],
            droppable: ['table', 'tbody', 'thead', 'tfoot'],
            classes: ['table-responsive']
        },

        initialize(...args) {
            this.constructor.__super__.initialize.apply(this, args);

            const components = this.get('components');
            if (!components.length) {
                components.add({
                    type: 'table'
                });
            }
        }
    },

    isComponent(el) {
        let result = null;

        if (el.tagName === 'DIV' && el.classList.contains(this.componentType)) {
            result = {
                type: this.componentType
            };
        }

        return result;
    }
});

export default TableResponsiveTypeBuilder;
