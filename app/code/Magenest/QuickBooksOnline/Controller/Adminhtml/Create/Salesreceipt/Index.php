<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateSalesReceipt;

/**
 * Class Index
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\SalesReceipt
 */
class Index extends AbstractCreateSalesReceipt
{
    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('Add all Sales Receipt to Magento need many times to execute'));
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend((__('List Sales Receipt')));

        return $resultPage;
    }
}
