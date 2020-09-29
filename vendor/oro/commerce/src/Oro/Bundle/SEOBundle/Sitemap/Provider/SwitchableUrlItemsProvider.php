<?php

namespace Oro\Bundle\SEOBundle\Sitemap\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\RedirectBundle\Generator\CanonicalUrlGenerator;
use Oro\Component\Website\WebsiteInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides Switchable UrlItems for sitemap generation
 */
class SwitchableUrlItemsProvider extends UrlItemsProvider
{
    /**
     * @var string
     */
    protected $excludeProviderKey;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param CanonicalUrlGenerator $canonicalUrlGenerator
     * @param ConfigManager $configManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $registry
     */
    public function __construct(
        CanonicalUrlGenerator $canonicalUrlGenerator,
        ConfigManager $configManager,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $registry
    ) {
        $this->configManager = $configManager;

        parent::__construct($canonicalUrlGenerator, $configManager, $eventDispatcher, $registry);
    }

    /**
     * @param string $excludeProviderKey
     */
    public function setExcludeProviderKey(string $excludeProviderKey)
    {
        $this->excludeProviderKey = $excludeProviderKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlItems(WebsiteInterface $website, $version)
    {
        if ($this->configManager->get($this->excludeProviderKey, false, false, $website)) {
            return [];
        }

        return parent::getUrlItems($website, $version);
    }
}
