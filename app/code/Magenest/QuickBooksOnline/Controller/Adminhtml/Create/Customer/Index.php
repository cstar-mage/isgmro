<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateCustomer;

/**
 * Class Index
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Index extends AbstractCreateCustomer
{
    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('Add All Customers to Magento need many times to execute'));
        $this->messageManager->addNoticeMessage(__('Password of customer is default , which is "quickbooks1234"'));
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend((__('List Customer')));

        return $resultPage;
    }
}
