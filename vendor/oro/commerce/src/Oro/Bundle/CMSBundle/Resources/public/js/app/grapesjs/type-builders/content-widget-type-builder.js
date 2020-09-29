import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import BaseTypeBuilder from 'orocms/js/app/grapesjs/type-builders/base-type-builder';
import DialogWidget from 'oro/dialog-widget';
import template from 'tpl-loader!orocms/templates/grapesjs-content-widget.html';
import routing from 'routing';

/**
 * Insert string into string via position
 * @param str
 * @param insert
 * @param startOffset
 * @param endOffset
 * @returns {string}
 */
function insetIntoString(str, insert, startOffset, endOffset) {
    return [str.slice(0, startOffset), insert, str.slice(endOffset)].join('');
}

function createDialog(gridName, editor, onClose) {
    const container = editor.Commands.isActive('fullscreen') ? editor.getEl() : 'body';
    const dialogOptions = {
        modal: true,
        resizable: true,
        autoResize: true,
        appendTo: container
    };

    if (_.isFunction(onClose)) {
        dialogOptions.close = onClose;
    }

    return new DialogWidget({
        title: __('oro.cms.wysiwyg.content_widget.title'),
        url: routing.generate('oro_datagrid_widget', {gridName}),
        loadingElement: container,
        dialogOptions
    });
}

/**
 * Content widget type builder
 */
const ContentWidgetTypeBuilder = BaseTypeBuilder.extend({
    button: {
        label: __('oro.cms.wysiwyg.component.content_widget'),
        category: 'Basic',
        attributes: {
            'class': 'fa fa-object-ungroup'
        }
    },

    commandName: null,

    editorEvents: {
        'component:selected': 'onSelect',
        'component:deselected': 'onDeselect',
        'canvas:drop': 'onDrop'
    },

    modelMixin: {
        defaults: {
            tagName: 'div',
            classes: ['content-widget', 'content-placeholder'],
            contentWidget: null,
            droppable: false,
            editable: false,
            stylable: false
        },

        initialize(...args) {
            if (this.get('tagName') === 'span') {
                this.set('draggable', false);
            }

            this.constructor.__super__.initialize.call(this, ...args);

            const toolbar = this.get('toolbar');
            const commandName = this.getSettingsCommandName();
            const commandExists = _.some(toolbar, {
                command: commandName
            });

            if (!commandExists) {
                toolbar.unshift({
                    attributes: {
                        'class': 'fa fa-gear',
                        'label': __('oro.cms.wysiwyg.toolbar.widgetSetting')
                    },
                    command: commandName
                });

                this.set('toolbar', toolbar);
            }

            this.listenTo(this, 'change:contentWidget', this.onContentBlockChange, this);
        },

        onContentBlockChange(model, contentWidget) {
            this.set('attributes', {
                'data-title': contentWidget.get('name'),
                'data-type': contentWidget.get('widgetType')
            });

            this.set('content', '{{ widget("' + contentWidget.get('name') + '") }}');
            this.view.render();
        },

        getSettingsCommandName() {
            return this.get('tagName') === 'span' ? 'inline-content-widget-settings' : 'content-widget-settings';
        }
    },

    viewMixin: {
        events: {
            dblclick: 'onDoubleClick'
        },

        onRender() {
            const contentWidget = this.model.get('contentWidget');
            let {name: title, widgetType} = contentWidget || {};

            if (!contentWidget) {
                title = this.$el.attr('data-title');
                widgetType = this.$el.attr('data-type');
            } else if (contentWidget.cid) {
                title = contentWidget.get('name');
                widgetType = contentWidget.get('widgetType');
            }

            this.$el.html(template({
                inline: this.$el.prop('tagName') === 'SPAN',
                title,
                widgetType
            }));
        },

        onDoubleClick(e) {
            this.em.get('Commands').run(this.model.getSettingsCommandName());

            e.stopPropagation();
        }
    },

    commands: {
        'content-widget-settings': (editor, sender, event) => {
            const gridName = 'cms-block-content-widget-grid';
            const dialog = createDialog(gridName, editor, function() {
                if (event.cid && !event.get('contentWidget')) {
                    event.remove();
                }
            });

            dialog.on('contentLoad', (data, widget) => {
                const gridWidget = widget.componentManager.get(gridName);
                gridWidget.grid.columns.remove(_.last(gridWidget.grid.columns.models));
            });

            dialog.on('grid-row-select', data => {
                let sel = editor.getSelected();
                if (event.cid) {
                    sel = event;
                }

                sel.set('contentWidget', data.model);
                dialog.remove();
            });

            dialog.render();
        },
        'inline-content-widget-settings': (editor, sender, event) => {
            const gridName = 'cms-inline-content-widget-grid';
            const dialog = createDialog(gridName, editor);

            dialog.on('grid-row-select', data => {
                const sel = editor.getSelected();
                if (event && event.selection) {
                    const originalText = event.selection.anchorNode.innerHTML;

                    const offset = originalText.indexOf(event.nodeValue);

                    if (offset > 0) {
                        event.offset += offset;
                        event.extentOffset += offset;
                    }

                    sel.components(
                        insetIntoString(
                            originalText
                            , `<span
                                data-title="${data.model.get('name')}"
                                data-type="${data.model.get('widgetType')}"
                                class="content-widget"
                                >{{ widget("${data.model.get('name')}") }}</span>`
                            , event.offset, event.extentOffset
                        )
                    );
                } else {
                    sel.onContentBlockChange(sel, data.model);
                }

                dialog.remove();
            });

            dialog.render();
        }
    },

    /**
     * @inheritDoc
     */
    constructor: function ContentWidgetTypeBuilder(options) {
        ContentWidgetTypeBuilder.__super__.constructor.call(this, options);
    },

    onInit() {
        this.editor.RichTextEditor.remove('inlineWidget');
        this.editor.RichTextEditor.add('inlineWidget', {
            icon: '<i class="fa fa-object-ungroup"></i>',
            attributes: {
                title: 'Inline widget'
            },
            result: rte => {
                const selection = rte.selection();
                const offset = rte.selection().anchorOffset;
                const extentOffset = rte.selection().extentOffset;
                const nodeValue = rte.selection().anchorNode.nodeValue;

                this.editor.runCommand('inline-content-widget-settings', {
                    selection,
                    offset,
                    extentOffset,
                    nodeValue
                });
            }
        });
    },

    onDrop(DataTransfer, model) {
        if (model instanceof this.Model) {
            this.editor.runCommand(model.getSettingsCommandName(), model);
        }
    },

    onSelect(model) {
        if (model instanceof this.Model) {
            this.editor.RichTextEditor.actionbar.hidden = true;
        }
    },

    onDeselect(model) {
        if (model instanceof this.Model) {
            this.editor.RichTextEditor.actionbar.hidden = false;
        }
    },

    isComponent(el) {
        let result = null;

        if ((el.tagName === 'DIV' || el.tagName === 'SPAN') && el.classList.contains('content-widget')) {
            result = {
                type: this.componentType
            };
        }

        return result;
    }
});

export default ContentWidgetTypeBuilder;
