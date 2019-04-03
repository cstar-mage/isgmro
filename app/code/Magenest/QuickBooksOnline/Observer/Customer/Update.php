<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksOnline extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_QuickBooksOnline
 * @author   Magenest JSC
 */
namespace Magenest\QuickBooksOnline\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Update
 *
 * @package Magenest\QuickBooksOnline\Observer\Customer
 */
class Update extends AbstractCustomer implements ObserverInterface
{

    /**
     * Cutomer edit information address
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->isConnected() && $this->isConnected() == 1) {
            try {
                /** @var \Magento\Customer\Model\Customer $customer */
                $customer = $observer->getEvent()->getCustomerAddress()->getCustomer();
                $id = $customer->getId();
                if ($id && $this->isEnabled()) {
                    if ($this->isImmediatelyMode()) {
                        $this->_customer->sync($id, true);
                    } else {
                        $this->addToQueue($id);
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Have an error when save your new information'));
            }
        }
    }
}
