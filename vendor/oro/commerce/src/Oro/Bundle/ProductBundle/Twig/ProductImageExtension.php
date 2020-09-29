<?php

namespace Oro\Bundle\ProductBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\LayoutBundle\Provider\Image\ImagePlaceholderProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductImage;
use Oro\Bundle\ProductBundle\Helper\ProductImageHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to get image placeholder and type images for a product entity:
 *   - collect_product_images_by_types
 *   - sort_product_images
 *   - product_image_placeholder
 */
class ProductImageExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    const NAME = 'oro_product_image';

    /** @var ContainerInterface */
    private $container;

    /** @var ImagePlaceholderProviderInterface */
    private $imagePlaceholderProvider;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ProductImageHelper
     */
    protected function getProductImageHelper()
    {
        return $this->container->get('oro_product.helper.product_image_helper');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'collect_product_images_by_types',
                [$this, 'collectProductImagesByTypes']
            ),
            new TwigFunction('sort_product_images', [$this, 'sortProductImages']),
            new TwigFunction('product_image_placeholder', [$this, 'getProductImagePlaceholder'])
        ];
    }

    /**
     * @param Product $product
     * @param array $imageTypes
     * @return ProductImage[]
     */
    public function collectProductImagesByTypes(Product $product, array $imageTypes): array
    {
        $result = [];
        $productImages = $product->getImages();
        if ($productImages->isEmpty()) {
            return $result;
        }

        /** @var ProductImage[] $result */
        foreach ($imageTypes as $imageType) {
            foreach ($product->getImagesByType($imageType) as $productImage) {
                $result[$productImage->getId()] = $productImage;
            }
        }

        return $this->getProductImageHelper()->sortImages($result);
    }

    /**
     * @param Collection $productImages
     * @return ProductImage[]
     */
    public function sortProductImages(Collection $productImages): array
    {
        return $this->getProductImageHelper()->sortImages($productImages->toArray());
    }

    /**
     * @param string $filter
     * @return string
     */
    public function getProductImagePlaceholder(string $filter): string
    {
        if (!$this->imagePlaceholderProvider) {
            $this->imagePlaceholderProvider = $this->container->get('oro_product.provider.product_image_placeholder');
        }

        return $this->imagePlaceholderProvider->getPath($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_product.provider.product_image_placeholder' => ImagePlaceholderProviderInterface::class,
            'oro_product.helper.product_image_helper' => ProductImageHelper::class,
        ];
    }
}
