<?php
declare(strict_types=1);

namespace Practice\CartRestriction\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Practice\Shipping\Model\Carrier\Validator;
use Psr\Log\LoggerInterface;

class ProductRestrictionAddToCart implements ObserverInterface
{
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
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): ProductRestrictionAddToCart
    {
        /** @var $request RequestInterface; */
        $request = $observer->getRequest();
        $params = $request->getParams();

        if (isset($params['product']) && $params['product'] && $this->validator->hasQuoteItemQuantity() > 0) {
            if ($this->validator->isCustomProductTypeInQuote()
                && !$this->validator->isCustomProductTypeExists((int)$params['product'])) {
                $this->messageManager
                    ->addErrorMessage(__('Your cart has some of custom product type, the other product cannot be buy with this product.'));
                $observer->getRequest()->setParam('product', false);
            }
        }

        return $this;
    }
}
