<?php
/**
 * Practice CollectRates
 */
declare(strict_types=1);

namespace Practice\Shipping\Plugin\Model\Quote\Address\RateCollector;

use Magento\Catalog\Model\Product;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\Result;
use Practice\ProductType\Model\Product\Type\CustomProduct;
use Practice\Shipping\Model\Carrier\CustomShipping;
use Practice\Shipping\Model\Carrier\Validator;
use Psr\Log\LoggerInterface;

class CustomProductTypeRestrictionPlugin
{
    public function __construct(
        private readonly ErrorFactory $errorFactory,
        protected SessionManagerInterface $checkoutSession,
        protected CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger,
        private readonly Validator $validator
    ) {
    }

    public function aroundCollectRates(
        RateCollectorInterface $subject,
        callable $proceed,
        RateRequest $rateRequest
    ): RateCollectorInterface|null {
        if ($this->validateRateRequest() === false) {
            $rates = $subject->getResult()->getAllRates();
            /** @var Result $result */
            $result = $subject->getResult();
            foreach ($rates as $rate) {

                if ($rate->getMethod() === CustomShipping::CODE) {
                    $result->reset()->append($rate);
                    return $subject;
                }
            }
        }

        return $proceed($rateRequest);
    }

    private function validateRateRequest(): bool
    {
        try {
            return $this->validator->isCustomProductTypeInQuote();
        } catch (\Exception) {
            return false;
        }
    }
}
