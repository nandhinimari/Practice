<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Practice\ProductType\Model\Product\Type;

/**
 * Custom product type implementation
 */
class CustomProduct extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'custom_product';

    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
