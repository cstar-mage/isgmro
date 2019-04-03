<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractUpdateCustomer;

/**
 * Class Index
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Customer
 */
class Index extends AbstractUpdateCustomer
{
    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend((__('Update Customer')));

        return $resultPage;
    }
}
