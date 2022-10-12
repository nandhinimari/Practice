<?php
/**
 * Practice CollectRates
 */
declare(strict_types=1);

namespace Practice\Shipping\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;
use Practice\ProductType\Model\Product\Type\CustomProduct;
use Practice\Shipping\Model\Carrier\CustomShipping;
use Psr\Log\LoggerInterface;

class ShippingMethodRestrictionPlugin
{
    private bool $renderingShippingMethod = false;

    public function __construct(
        protected SessionManagerInterface $checkoutSession,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Hide specific shipping methods
     */
    public function afterEstimateByExtendedAddress(ShipmentEstimationInterface $subject, $methods): mixed
    {
        foreach ($methods as $key => &$method) {
            if ($method->getMethodCode() == CustomShipping::CODE) {
                if (!$this->checkShippingMethodByProductType()) {
                    unset($methods[$key]);
                }
            }
        }
        return $methods;
    }

    private function checkShippingMethodByProductType(): mixed
    {
        $items = $this->getQuote()->getAllItems();

        foreach ($items as $item) {
            /**
             * @var $product Product
             */
            $product = $item->getProduct();

            if ((string)$product->getTypeId()  === CustomProduct::TYPE_CODE) {
                $this->renderingShippingMethod = true;
            }
        }

        return $this->renderingShippingMethod;
    }

    private function getQuote(): Quote
    {
        return $this->checkoutSession->getQuote();
    }
}
