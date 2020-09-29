<?php

namespace Oro\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\OrderBundle\Manager\OrderAddressManager;
use Oro\Bundle\OrderBundle\Provider\OrderAddressSecurityProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents order address select form type with list of grouped available addresses for customer user
 */
class OrderAddressSelectType extends AbstractType
{
    const NAME = 'oro_order_address_select';
    const ENTER_MANUALLY = 0;
    const FORMAT_ADDRESS_SEPARATOR = ', ';
    const DEFAULT_GROUP_LABEL_PREFIX = 'oro.order.';

    /** @var OrderAddressManager */
    private $addressManager;

    /** @var AddressFormatter */
    private $addressFormatter;

    /** @var OrderAddressSecurityProvider */
    private $addressSecurityProvider;

    /** @var Serializer */
    private $serializer;

    /**
     * @param OrderAddressManager $addressManager
     * @param AddressFormatter $addressFormatter
     * @param OrderAddressSecurityProvider $addressSecurityProvider
     * @param Serializer $serializer
     */
    public function __construct(
        OrderAddressManager $addressManager,
        AddressFormatter $addressFormatter,
        OrderAddressSecurityProvider $addressSecurityProvider,
        Serializer $serializer
    ) {
        $this->addressManager = $addressManager;
        $this->addressFormatter = $addressFormatter;
        $this->addressSecurityProvider = $addressSecurityProvider;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object = $options['object'];
        $addressType = $options['address_type'];

        /** @var CustomerOwnerAwareInterface $object */
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($object, $addressType) {
            $address = $event->getData();

            if ($address === self::ENTER_MANUALLY) {
                return;
            }

            $event->setData(null);
            if ($address) {
                $event->setData($this->addressManager->updateFromAbstract($address));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $collection = $options['address_collection'];

        $view->vars['attr']['data-addresses'] = json_encode($this->getPlainData($collection->toArray()));
        $view->vars['attr']['data-default'] = $collection->getDefaultAddressKey();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['object', 'address_type'])
            ->setDefaults([
                'data_class' => OrderAddress::class,
                'label' => false,
                'configs' => function (Options $options) {
                    return [
                        'placeholder' => $options['group_label_prefix'] . 'form.address.choose',
                    ];
                },
                'address_collection' => function (Options $options) {
                    return $this->addressManager->getGroupedAddresses(
                        $options['object'],
                        $options['address_type'],
                        $options['group_label_prefix']
                    );
                },
                'choice_loader' => function (Options $options) {
                    return new CallbackChoiceLoader(function () use ($options) {
                        $collection = $options['address_collection'];
                        $choices = $collection->toArray();

                        $isGranted = $this->addressSecurityProvider->isManualEditGranted($options['address_type']);
                        if ($isGranted) {
                            $choices[$options['group_label_prefix'] . 'form.address.manual'] = self::ENTER_MANUALLY;
                        }

                        return $choices;
                    });
                },
                'choice_value' => function ($choice) {
                    if (is_scalar($choice)) {
                        return $choice;
                    } elseif ($choice instanceof CustomerAddress || $choice instanceof CustomerUserAddress) {
                        return $this->addressManager->getIdentifier($choice);
                    }

                    return null;
                },
                'choice_label' => function ($choice, $key) {
                    if ($choice instanceof AbstractAddress) {
                        return $this->addressFormatter->format($choice, null, self::FORMAT_ADDRESS_SEPARATOR);
                    }

                    return $key;
                },
                'group_label_prefix' => self::DEFAULT_GROUP_LABEL_PREFIX,
            ])
            ->setAllowedValues('address_type', [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING])
            ->setAllowedTypes('object', CustomerOwnerAwareInterface::class);
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
        return Select2ChoiceType::class;
    }

    /**
     * @param array $addresses
     *
     * @return array
     */
    private function getPlainData(array $addresses = [])
    {
        $data = [];

        array_walk_recursive($addresses, function ($item, $key) use (&$data) {
            if ($item instanceof AbstractAddress) {
                $data[$key] = $this->serializer->normalize($item);
            }
        });

        return $data;
    }

    /**
     * @param OrderAddress|null $checkoutAddress
     *
     * @return int|string
     */
    private function getSelectedAddressKey(OrderAddress $checkoutAddress = null)
    {
        $selectedKey = null;
        if ($checkoutAddress) {
            $selectedKey = OrderAddressSelectType::ENTER_MANUALLY;
            if ($checkoutAddress->getCustomerAddress()) {
                $selectedKey = $this->addressManager->getIdentifier($checkoutAddress->getCustomerAddress());
            } elseif ($checkoutAddress->getCustomerUserAddress()) {
                $selectedKey = $this->addressManager->getIdentifier($checkoutAddress->getCustomerUserAddress());
            }
        }

        return $selectedKey;
    }
}
