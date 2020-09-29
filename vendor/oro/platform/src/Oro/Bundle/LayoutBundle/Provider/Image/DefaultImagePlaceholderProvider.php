<?php

namespace Oro\Bundle\LayoutBundle\Provider\Image;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * Provides the path to the default image placeholder.
 */
class DefaultImagePlaceholderProvider implements ImagePlaceholderProviderInterface
{
    /** @var CacheManager */
    private $imagineCacheManager;

    /** @var string */
    private $defaultPath;

    /**
     * @param CacheManager $imagineCacheManager
     * @param string $defaultPath
     */
    public function __construct(CacheManager $imagineCacheManager, string $defaultPath)
    {
        $this->imagineCacheManager = $imagineCacheManager;
        $this->defaultPath = $defaultPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(string $filter): ?string
    {
        return $this->imagineCacheManager->getBrowserPath($this->defaultPath, $filter);
    }
}
