<?php

namespace Oro\Bundle\CheckoutBundle\Acl\Voter;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\SecurityBundle\Acl\Voter\EntityClassResolverUtil;
use Oro\Component\Checkout\Entity\CheckoutSourceEntityInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks whether it is allowed to create the Checkout entity
 * from an entity that implements CheckoutSourceEntityInterface.
 */
class CheckoutVoter implements VoterInterface
{
    const ATTRIBUTE_CREATE = 'CHECKOUT_CREATE';

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!is_object($object)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(self::ATTRIBUTE_CREATE, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!is_a(EntityClassResolverUtil::getEntityClass($object), CheckoutSourceEntityInterface::class, true)) {
            return self::ACCESS_ABSTAIN;
        }

        if ($this->authorizationChecker->isGranted(BasicPermissionMap::PERMISSION_VIEW, $object)
            && $this->authorizationChecker->isGranted(
                BasicPermissionMap::PERMISSION_CREATE . ';entity:' . Checkout::class
            )
        ) {
            return self::ACCESS_GRANTED;
        }

        return self::ACCESS_DENIED;
    }
}
