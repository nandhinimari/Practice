<?php
declare(strict_types=1);

namespace Practice\CartRestriction\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote;
use Practice\ProductType\Model\Product\Type\CustomProduct;
use Practice\Shipping\Model\Carrier\Validator;
use Psr\Log\LoggerInterface;

class ClearCartItems implements ObserverInterface
{
    public bool $deletedItem = false;

    /**
     * @param ManagerInterface $messageManager
     * @param SessionManagerInterface $checkoutSession
     * @param Validator $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ManagerInterface $messageManager,
        protected SessionManagerInterface $checkoutSession,
        private readonly Validator $validator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): ClearCartItems
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');

        if ($product && $product->getId() && $this->validator->hasQuoteItemQuantity() > 0) {
            if ($this->validator->isCustomProductTypeExists((int)$product->getId())) {
                $this->deleteQuoteItems($product);

                if ($this->deletedItem) {
                    $this->messageManager->addNoticeMessage(__('We have removed the other product type in your cart'));
                }
            }
        }

        return $this;
    }

    public function deleteQuoteItems($product): void
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $productType = $product->getTypeId();

        if ($productType == CustomProduct::TYPE_CODE) {
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductType() !== CustomProduct::TYPE_CODE) {
                    $this->deletedItem = true;
                    $itemId = $item->getItemId();
                    $quote->removeItem($itemId);
                }
            }
            $quote->setTotalsCollectedFlag(false);
        }
    }
}
