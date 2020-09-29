<?php

namespace Oro\Bundle\CMSBundle\Acl\Voter;

use Oro\Bundle\CMSBundle\Entity\ContentWidget;
use Oro\Bundle\CMSBundle\Entity\ContentWidgetUsage;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks if given ContentWidget can be deleted.
 */
class ContentWidgetVoter implements VoterInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$subject instanceof ContentWidget) {
            return self::ACCESS_ABSTAIN;
        }

        $repository = $this->doctrineHelper->getEntityRepositoryForClass(ContentWidgetUsage::class);

        foreach ($attributes as $attribute) {
            if ($attribute !== 'DELETE') {
                continue;
            }

            if ($repository->findOneBy(['contentWidget' => $subject])) {
                return self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_ABSTAIN;
    }
}
