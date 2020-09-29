<?php

namespace Oro\Bundle\CheckoutBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CheckoutBundle\DependencyInjection\Configuration;
use Oro\Bundle\CheckoutBundle\DependencyInjection\OroCheckoutExtension;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Provider\DefaultUserProvider;

/**
 * Schedules extra update for the completed data of Checkout entity.
 * Sets the owner and the organization for Checkout entity in case of Guest user.
 */
class CheckoutListener
{
    /** @var DefaultUserProvider */
    private $defaultUserProvider;

    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    /**
     * @param DefaultUserProvider $defaultUserProvider
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(
        DefaultUserProvider $defaultUserProvider,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->defaultUserProvider = $defaultUserProvider;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param Checkout $checkout
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(Checkout $checkout, LifecycleEventArgs $event)
    {
        $unitOfWork = $event->getEntityManager()->getUnitOfWork();

        $unitOfWork->scheduleExtraUpdate(
            $checkout,
            [
                'completedData' => [null, $checkout->getCompletedData()]
            ]
        );
    }

    /**
     * @param Checkout $checkout
     */
    public function prePersist(Checkout $checkout)
    {
        if ($this->tokenAccessor->getToken() instanceof AnonymousCustomerUserToken
            && null === $checkout->getOwner()
        ) {
            $checkout->setOwner($this->defaultUserProvider->getDefaultUser(
                OroCheckoutExtension::ALIAS,
                Configuration::DEFAULT_GUEST_CHECKOUT_OWNER
            ));

            $organization = $this->tokenAccessor->getOrganization();
            if (null !== $organization) {
                $checkout->setOrganization($organization);
            }
        }
    }
}
