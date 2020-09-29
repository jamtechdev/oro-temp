<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\Cache;
use Oro\Bundle\SearchBundle\Configuration\MappingConfigurationProvider;
use Oro\Bundle\SearchBundle\Event\SearchMappingCollectEvent;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SearchMappingProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    protected $testMapping = [
        'Oro\TestBundle\Entity\TestEntity' => [
            'alias'  => 'test_entity',
            'fields' => [
                [
                    'name'           => 'firstname',
                    'target_type'    => 'text',
                    'target_columns' => ['firstname']
                ],
                [
                    'name'           => 'qty',
                    'target_type'    => 'integer',
                    'target_columns' => ['qty']
                ]
            ]
        ]
    ];

    /** @var Cache|\PHPUnit\Framework\MockObject\MockObject */
    protected $cache;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventDispatcher;

    /** @var MappingConfigurationProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->cache = $this->createMock(Cache::class);
        $this->configProvider = $this->createMock(MappingConfigurationProvider::class);

        $this->configProvider->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($this->testMapping);
    }

    public function testGetEntitiesListAliases()
    {
        $this->assertEquals(
            ['Oro\TestBundle\Entity\TestEntity' => 'test_entity'],
            $this->getProvider()->getEntitiesListAliases()
        );
    }

    public function testGetEntityAliases()
    {
        $this->assertEquals(
            ['Oro\TestBundle\Entity\TestEntity' => 'test_entity'],
            $this->getProvider()->getEntityAliases(['Oro\TestBundle\Entity\TestEntity'])
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The search alias for the entity "Oro\TestBundle\Entity\UnknownEntity" not found.
     */
    public function testGetEntityAliasesForUnknownEntity()
    {
        $this->getProvider()->getEntityAliases(
            ['Oro\TestBundle\Entity\TestEntity', 'Oro\TestBundle\Entity\UnknownEntity']
        );
    }

    public function testGetEntityAliasesForEmptyClassNames()
    {
        $this->assertEquals(
            ['Oro\TestBundle\Entity\TestEntity' => 'test_entity'],
            $this->getProvider()->getEntityAliases()
        );
    }

    public function testGetEntityAlias()
    {
        $this->assertEquals(
            'test_entity',
            $this->getProvider()->getEntityAlias('Oro\TestBundle\Entity\TestEntity')
        );
    }

    public function testGetEntityAliasForUnknownEntity()
    {
        $this->assertNull(
            $this->getProvider()->getEntityAlias('Oro\TestBundle\Entity\UnknownEntity')
        );
    }

    public function testGetEntityClasses()
    {
        $this->assertEquals(
            ['Oro\TestBundle\Entity\TestEntity'],
            $this->getProvider()->getEntityClasses()
        );
    }

    public function testIsClassSupported()
    {
        $provider = $this->getProvider();

        $this->assertTrue($provider->isClassSupported('Oro\TestBundle\Entity\TestEntity'));
        $this->assertFalse($provider->isClassSupported('Oro\TestBundle\Entity\BadEntity'));
    }

    public function testHasFieldsMapping()
    {
        $provider = $this->getProvider();

        $this->assertTrue($provider->hasFieldsMapping('Oro\TestBundle\Entity\TestEntity'));
        $this->assertFalse($provider->hasFieldsMapping('Oro\TestBundle\Entity\BadEntity'));
    }

    public function testGetEntityMapParameter()
    {
        $provider = $this->getProvider();

        $this->assertEquals(
            'test_entity',
            $provider->getEntityMapParameter('Oro\TestBundle\Entity\TestEntity', 'alias')
        );
        $this->assertFalse(
            $provider->getEntityMapParameter('Oro\TestBundle\Entity\TestEntity', 'badParameter', false)
        );
    }

    public function testGetEntityClass()
    {
        $this->assertEquals(
            'Oro\TestBundle\Entity\TestEntity',
            $this->getProvider()->getEntityClass('test_entity')
        );
    }

    public function testGetEntityClassForUnknownAlias()
    {
        $this->assertNull(
            $this->getProvider()->getEntityClass('unknown_entity')
        );
    }

    public function testGetMappingConfigCached()
    {
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertEquals($this->testMapping, $this->getProvider()->getMappingConfig());
    }

    public function testGetMappingConfigWhenConfigurationNotLoaded()
    {
        $configTimestamp = 20;

        $this->configProvider->expects($this->never())
            ->method('isCacheFresh');
        $this->configProvider->expects($this->once())
            ->method('getCacheTimestamp')
            ->willReturn($configTimestamp);

        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('oro_search.mapping_config')
            ->willReturn(false);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($eventName, SearchMappingCollectEvent $event) {
                $this->assertEquals(SearchMappingCollectEvent::EVENT_NAME, $eventName);
                $this->assertEquals($this->testMapping, $event->getMappingConfig());

                $event->setMappingConfig([]);
            });

        $this->cache->expects($this->once())
            ->method('save')
            ->with('oro_search.mapping_config', [$configTimestamp, []]);

        $provider = $this->getProvider(false);
        $this->assertEquals([], $provider->getMappingConfig());
        $this->assertEquals([], $provider->getMappingConfig());
    }

    public function testGetMappingConfigWhenConfigurationChanged()
    {
        $cacheTimestamp = 10;
        $configTimestamp = 20;

        $this->configProvider->expects($this->once())
            ->method('isCacheFresh')
            ->with($cacheTimestamp)
            ->willReturn(false);
        $this->configProvider->expects($this->once())
            ->method('getCacheTimestamp')
            ->willReturn($configTimestamp);

        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('oro_search.mapping_config')
            ->willReturn([$cacheTimestamp, []]);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($eventName, SearchMappingCollectEvent $event) {
                $this->assertEquals(SearchMappingCollectEvent::EVENT_NAME, $eventName);
                $this->assertEquals($this->testMapping, $event->getMappingConfig());

                $event->setMappingConfig([]);
            });

        $this->cache->expects($this->once())
            ->method('save')
            ->with('oro_search.mapping_config', [$configTimestamp, []]);

        $provider = $this->getProvider(false);
        $this->assertEquals([], $provider->getMappingConfig());
        $this->assertEquals([], $provider->getMappingConfig());
    }

    public function testGetMappingConfigWhenConfigurationNotChanged()
    {
        $cacheTimestamp = 10;

        $this->configProvider->expects($this->once())
            ->method('isCacheFresh')
            ->with($cacheTimestamp)
            ->willReturn(true);
        $this->configProvider->expects($this->never())
            ->method('getCacheTimestamp');

        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('oro_search.mapping_config')
            ->willReturn([$cacheTimestamp, []]);

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->cache->expects($this->never())
            ->method('save');

        $provider = $this->getProvider(false);
        $this->assertEquals([], $provider->getMappingConfig());
        $this->assertEquals([], $provider->getMappingConfig());
    }

    public function testClearCache()
    {
        $this->cache->expects($this->once())
            ->method('delete')
            ->with('oro_search.mapping_config');
        $this->cache->expects($this->never())
            ->method('fetch');

        $this->configProvider->expects($this->never())
            ->method('clearCache');
        $this->configProvider->expects($this->never())
            ->method('warmUpCache');

        $provider = $this->getProvider(false);
        $provider->clearCache();

        self::assertAttributeSame(null, 'configuration', $provider);
    }

    public function testWarmUpCache()
    {
        $cacheTimestamp = 10;
        $config = [
            'Oro\TestBundle\Entity\TestEntity' => [
                'alias'  => 'test_entity',
                'fields' => [
                    ['name' => 'firstname', 'target_type' => 'text']
                ]
            ]
        ];

        $this->configProvider->expects($this->once())
            ->method('isCacheFresh')
            ->with($cacheTimestamp)
            ->willReturn(true);

        $this->cache->expects($this->once())
            ->method('delete')
            ->with('oro_search.mapping_config');
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('oro_search.mapping_config')
            ->willReturn([$cacheTimestamp, $config]);

        $this->configProvider->expects($this->never())
            ->method('clearCache');
        $this->configProvider->expects($this->never())
            ->method('warmUpCache');

        $provider = $this->getProvider(false);
        $provider->warmUpCache();

        self::assertAttributeEquals($config, 'configuration', $provider);
    }

    /**
     * @param bool $mockFetch
     *
     * @return SearchMappingProvider
     */
    protected function getProvider($mockFetch = true)
    {
        $provider = new SearchMappingProvider(
            $this->eventDispatcher,
            $this->configProvider,
            $this->cache
        );
        if ($mockFetch) {
            $this->configProvider->expects($this->once())
                ->method('isCacheFresh')
                ->with(self::isNull())
                ->willReturn(true);
            $this->cache->expects($this->once())
                ->method('fetch')
                ->with('oro_search.mapping_config')
                ->willReturn([null, $this->testMapping]);
        }

        return $provider;
    }
}
