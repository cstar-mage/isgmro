<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateProduct;

/**
 * Class Index
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Index extends AbstractCreateProduct
{
    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('Add all Products to Magento need many times to execute'));
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend((__('List Product')));

        return $resultPage;
    }
}
