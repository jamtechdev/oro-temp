<?php

namespace Oro\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Oro\Bundle\ProductBundle\Form\Type\ProductUnitSelectionType;
use Oro\Bundle\ProductBundle\Provider\ProductUnitsProvider;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineItemType extends AbstractOrderLineItemType
{
    const NAME = 'oro_order_line_item';

    /**
     * @var ProductUnitsProvider
     */
    protected $productUnitsProvider;

    /**
     * @param ProductUnitsProvider $productUnitsProvider
     */
    public function __construct(ProductUnitsProvider $productUnitsProvider)
    {
        $this->productUnitsProvider = $productUnitsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault(
            'page_component_options',
            [
                'view' => 'oroorder/js/app/views/line-item-view',
                'freeFormUnits' => $this->getFreeFormUnits(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'product',
                ProductSelectType::class,
                [
                    'error_bubbling' => true,
                    'required' => true,
                    'label' => 'oro.product.entity_label',
                    'create_enabled' => false,
                    'data_parameters' => [
                        'scope' => 'order'
                    ]
                ]
            )
            ->add(
                'productSku',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'oro.product.sku.label',
                ]
            )
            ->add(
                'freeFormProduct',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'oro.product.entity_label',
                ]
            )
            ->add(
                'price',
                PriceType::class,
                [
                    'error_bubbling' => false,
                    'required' => true,
                    'label' => 'oro.order.orderlineitem.price.label',
                    'hide_currency' => true,
                    'default_currency' => $options['currency'],
                ]
            )
            ->add('priceType', HiddenType::class, [
                'data' => OrderLineItem::PRICE_TYPE_UNIT,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateAvailableUnits(FormInterface $form)
    {
        /** @var OrderLineItem $item */
        $item = $form->getData();
        if (!$item->getProduct()) {
            return;
        }

        $form->remove('productUnit');
        $form->add(
            'productUnit',
            ProductUnitSelectionType::class,
            [
                'label' => 'oro.product.productunit.entity_label',
                'required' => true,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getFreeFormUnits()
    {
        return $this->productUnitsProvider->getAvailableProductUnitsWithPrecision();
    }
}
