<?php
namespace Practice\Shipping\Model\Carrier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Practice\ProductType\Model\Product\Type\CustomProduct;
use Psr\Log\LoggerInterface;

class Validator
{
    /**
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param SessionManagerInterface $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param CartItemRepositoryInterface $cartItemRepository
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        protected ProductRepositoryInterface $productRepository,
        protected SessionManagerInterface $checkoutSession,
        protected CartRepositoryInterface $cartRepository,
        protected CartItemRepositoryInterface $cartItemRepository,
    ) {
    }

    public function checkCustomProductTypeInQuote(CartInterface $quote): bool
    {
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            /** @var $product Product */
            $product = $item->getProduct();

            if ((string)$item->getProductType()  === CustomProduct::TYPE_CODE) {
                return true;
            }
        }

        return false;
    }

    public function isCustomProductTypeInQuote(): bool
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $quote = (!empty($quoteId)) ? $this->cartRepository->getActive($quoteId) : null;

        if (!$quote || $this->checkCustomProductTypeInQuote($quote) === false) {
            return false;
        }

        return true;
    }

    /**
     * Get product data
     */
    public function isCustomProductTypeExists(int $productId): bool
    {
        try {
            /** @var $product \Magento\Catalog\Api\Data\ProductInterface */
            $product = $this->productRepository->getById($productId);
            if ($product && (string)$product->getTypeId() === CustomProduct::TYPE_CODE) {
                return true;
            }
        } catch (NoSuchEntityException) {
            return false;
        }

        return false;
    }

    public function hasQuoteItemQuantity(): mixed
    {
        /**
         * @var $quote Quote
         */
        $quote = $this->checkoutSession->getQuote();
        return $quote->getItemsCount();
    }
}
