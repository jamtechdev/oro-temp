<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ProductBundle\Layout\DataProvider\FeaturedProductsProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FeaturedProductsProviderTest extends AbstractSegmentProductsProviderTest
{
    public function testGetProducts()
    {
        $this->prepare();

        $this->getProducts($this->getQueryBuilder());
    }

    public function testGetProductsWithCache()
    {
        $this->prepare();

        $this->getProductsWithCache();
    }

    public function testGetProductsWithInvalidCache(): void
    {
        $this->prepare();

        $this->getProductsWithInvalidCache($this->getQueryBuilder());
    }

    public function testGetProductsWithoutSegment()
    {
        $this->configManager
            ->expects($this->exactly(2))
            ->method('get')
            ->with('oro_product.featured_products_segment_id')
            ->willReturn(1);

        $this->getProductsWithoutSegment();
    }

    public function testGetProductsQueryBuilderIsNull()
    {
        $this->prepare();

        $this->getProductsQueryBuilderIsNull();
    }

    /**
     * @param ManagerRegistry $registry
     */
    protected function createSegmentProvider(ManagerRegistry $registry)
    {
        $this->segmentProductsProvider = new FeaturedProductsProvider(
            $this->segmentManager,
            $this->productSegmentProvider,
            $this->productManager,
            $this->configManager,
            $registry,
            $this->tokenStorage,
            $this->crypter,
            $this->aclHelper
        );
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return 'featured_products_0__1';
    }

    private function prepare()
    {
        $this->configManager
            ->expects($this->exactly(2))
            ->method('get')
            ->with('oro_product.featured_products_segment_id')
            ->willReturn(1);

        /** @var TokenInterface|\PHPUnit\Framework\MockObject\MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
    }
}
