<?php

namespace Oro\Bundle\CheckoutBundle\Form\Type;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\FrontendBundle\Form\Type\CountryType;
use Oro\Bundle\FrontendBundle\Form\Type\RegionType;
use Oro\Bundle\OrderBundle\Form\Type\OrderAddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents checkout address form type
 */
class CheckoutAddressType extends AbstractType
{
    const NAME = 'oro_checkout_address';
    const SHIPPING_ADDRESS_NAME = 'shipping_address';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customerAddress', CheckoutAddressSelectType::class, [
            'object' => $options['object'],
            'address_type' => $options['addressType'],
            'required' => true,
            'mapped' => false,
        ]);

        $builder->add('country', CountryType::class, [
            'required' => true,
            'label' => 'oro.address.country.label',
        ]);

        $builder->add('region', RegionType::class, [
            'required' => true,
            'label' => 'oro.address.region.label',
        ]);

        $builder->get('city')->setRequired(true);
        $builder->get('postalCode')->setRequired(true);
        $builder->get('street')->setRequired(true);
        $builder->get('customerAddress')->setRequired(true);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setAllowedTypes('object', Checkout::class);
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
    public function getParent()
    {
        return OrderAddressType::class;
    }
}
