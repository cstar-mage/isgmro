<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Observer\Customer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\QuickBooksOnline\Observer\Customer\AbstractCustomer;

/**
 * Class Edit
 * @package Magenest\QuickBooksOnline\Observer\Customer
 */
class Edit extends AbstractCustomer implements ObserverInterface
{

    /**
     * Admin edit information address
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->isConnected() && $this->isConnected() == 1) {
            try {
                /** @var \Magento\Customer\Model\Customer $customer */
                $customer = $observer->getEvent()->getCustomer();
                $id = $customer->getId();
                if ($id && $this->isEnabled()) {
                    if ($this->isImmediatelyMode()) {
                        $this->_customer->sync($id, true);
                        $this->messageManager->addSuccessMessage(
                            __('Updated success this customer(Id: %1) in QuickBooksOnline.', $id)
                        );
                    } else {
                        $this->addToQueue($id);
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                //TODO when have exception
            }
        }
    }
}
