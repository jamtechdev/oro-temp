<?php

namespace Oro\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;
use Oro\Bundle\MigrationBundle\Entity\Repository\DataFixtureRepository;

/**
 * Loading data for email templates
 */
class LoadEmailTemplates extends AbstractEmailFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // this migration was already loaded in previous versions, but in OroReminderBundle,
        // so it will be loaded again because of namespace change

        /** @var DataFixtureRepository $repo */
        $repo = $manager->getRepository('OroMigrationBundle:DataFixture');

        $isFixtureExists = $repo->isDataFixtureExists(
            'm.className = ?0',
            ['Oro\Bundle\ReminderBundle\Migrations\Data\ORM\LoadEmailTemplates']
        );
        if ($isFixtureExists) {
            $repo->updateDataFixutreHistory(
                [
                    'm.className' => "'Oro\Bundle\TaskBundle\Migrations\Data\ORM\LoadEmailTemplates'",
                ],
                'm.className = ?0',
                ['Oro\Bundle\ReminderBundle\Migrations\Data\ORM\LoadEmailTemplates']
            );
        } else {
            parent::load($manager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroTaskBundle/Migrations/Data/ORM/data/emails');
    }
}
