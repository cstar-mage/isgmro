<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Product;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractUpdateProduct;

/**
 * Class Index
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Product
 */
class Index extends AbstractUpdateProduct
{
    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend((__('Update Product')));

        return $resultPage;
    }
}
