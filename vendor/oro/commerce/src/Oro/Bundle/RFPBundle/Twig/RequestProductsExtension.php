<?php

namespace Oro\Bundle\RFPBundle\Twig;

use Oro\Bundle\RFPBundle\Entity\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to retrieve products from a request for quote:
 *   - rfp_products
 */
class RequestProductsExtension extends AbstractExtension
{
    const NAME = 'oro_rfp_request_products';

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [new TwigFunction('rfp_products', [$this, 'getRequestProducts'])];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getRequestProducts(Request $request)
    {
        $result = [];
        foreach ($request->getRequestProducts() as $requestProduct) {
            $product = $requestProduct->getProduct();
            $data['name'] = (string)$product;
            $data['sku'] = $requestProduct->getProductSku();
            $data['comment'] = $requestProduct->getComment();

            $items = [];
            foreach ($requestProduct->getRequestProductItems() as $productItem) {
                $items[$productItem->getId()] = [
                    'quantity' => $productItem->getQuantity(),
                    'price' => $productItem->getPrice(),
                    'unit' => $productItem->getProductUnitCode(),
                ];
            }

            $data['items'] = $items;

            $result[$product->getId()] = $data;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
