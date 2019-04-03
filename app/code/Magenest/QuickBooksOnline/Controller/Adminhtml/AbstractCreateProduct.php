<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magenest\QuickBooksOnline\Model\ProductFactory;

/**
 * Class AbstractCreateProduct
 *
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml
 */
abstract class AbstractCreateProduct extends Action
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * AbstractCreateProduct constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::create_product')
            ->addBreadcrumb(__('List Product'), __('List Product'));
        $resultPage->getConfig()->getTitle()->set(__('List Product'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::create_product');
    }
}
