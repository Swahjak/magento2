<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * @inheritdoc
 */
class GetProductIdsBySkus implements GetProductIdsBySkusInterface
{
    /**
     * @var array
     */
    private $productIdsBySkus = [];

    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @param ProductResourceModel $productResource
     */
    public function __construct(
        ProductResourceModel $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus): array
    {
        $result = [];

        foreach ($skus as $index => $sku) {
            if (!isset($this->productIdsBySkus[$sku])) {
                continue;
            }

            unset($skus[$index]);
            $result[$sku] = $this->productIdsBySkus[$sku];
        }

        if (empty($skus)) {
            return $result;
        }

        $result = array_replace($result, $this->productResource->getProductsIdsBySkus($skus));
        $notFoundSkus = array_diff($skus, array_keys($result));

        if (!empty($notFoundSkus)) {
            throw new NoSuchEntityException(
                __('Following products with requested skus were not found: %1', implode($notFoundSkus, ', '))
            );
        }

        $this->productIdsBySkus = array_replace($this->productIdsBySkus, $result);

        return $result;
    }
}
