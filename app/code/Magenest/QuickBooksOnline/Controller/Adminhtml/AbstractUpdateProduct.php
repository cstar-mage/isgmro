<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractUpdateProduct
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml
 */
abstract class AbstractUpdateProduct extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * AbstractConnection constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
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
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::update_product')
            ->addBreadcrumb(__('Update Product'), __('Update Product'));
        $resultPage->getConfig()->getTitle()->set(__('Update Product'));

        return $resultPage;
    }


    /**
     * Check Acl
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::update_product');
    }
}
