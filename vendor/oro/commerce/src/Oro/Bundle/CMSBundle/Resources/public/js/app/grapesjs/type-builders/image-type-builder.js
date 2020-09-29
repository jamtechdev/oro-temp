import __ from 'orotranslation/js/translator';
import BaseTypeBuilder from 'orocms/js/app/grapesjs/type-builders/base-type-builder';
import openDigitalAssetsCommand from 'orocms/js/app/grapesjs/modules/open-digital-assets-command';
import DigitalAssetHelper from 'orocms/js/app/grapesjs/helpers/digital-asset-helper';

const ImageTypeBuilder = BaseTypeBuilder.extend({
    parentType: 'image',

    modelMixin: {
        defaults: {
            tagName: 'img'
        }
    },

    viewMixin: {
        onActive: function(e) {
            if (e) {
                e.stopPropagation();
            }

            if (this.model.get('editable')) {
                this._openDigitalAssetManager(this.model);
            }
        },

        /**
         * @inheritDoc
         */
        updateAttributes: function(...args) {
            this.constructor.__super__.updateAttributes.apply(this, args);

            const imageSrc = DigitalAssetHelper.getImageUrlFromTwigTag(this.model.get('src'));

            if (imageSrc) {
                this.$el.attr('src', imageSrc);
            }
        },

        _openDigitalAssetManager: function(digitalAssetImageComponentModel) {
            this.em.get('Commands').run(
                'open-digital-assets',
                {
                    target: digitalAssetImageComponentModel,
                    title: __('oro.cms.wysiwyg.digital_asset.image.title'),
                    routeName: 'oro_digital_asset_widget_choose_image',
                    onSelect: function(digitalAssetModel) {
                        const {digitalAssetId, uuid, title} = digitalAssetModel.get('previewMetadata');

                        digitalAssetImageComponentModel
                            .setAttributes({alt: title || ''})
                            .set(
                                'src',
                                `{{ wysiwyg_image('${digitalAssetId}','${uuid}') }}`
                            );
                    }
                }
            );
        }
    },

    commands: {
        'open-digital-assets': openDigitalAssetsCommand
    },

    constructor: function ImageTypeBuilder(options) {
        ImageTypeBuilder.__super__.constructor.call(this, options);
    },

    createPanelButton() {
        if (this.editor.ComponentRestriction.isAllow(['img'])) {
            this.editor.BlockManager.add(this.componentType, {
                label: __('oro.cms.wysiwyg.component.digital_asset.image'),
                attributes: {
                    'class': 'fa fa-picture-o'
                }
            });
        }
    },

    registerEditorCommands() {
        if (this.editor.Commands.has('open-digital-assets')) {
            return;
        }

        ImageTypeBuilder.__super__.registerEditorCommands.call(this);
    }
});

export default ImageTypeBuilder;
