<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magenest\QuickBooksOnline\Model\SalesReceiptFactory;

/**
 * Class AbstractCreateInvoice
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml
 */
abstract class AbstractCreateInvoice extends Action
{
    /**
     * @var SalesReceiptFactory
     */
    protected $salesReceiptFactory;

    /**
     * AbstractCreateSalesReceipt constructor.
     * @param Context $context
     * @param SalesReceiptFactory $salesReceiptFactory
     */
    public function __construct(
        Context $context,
        SalesReceiptFactory $salesReceiptFactory
    ) {
        $this->salesReceiptFactory = $salesReceiptFactory;
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
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::create_invoice')
            ->addBreadcrumb(__('List Invoice'), __('List Invoice'));
        $resultPage->getConfig()->getTitle()->set(__('List Invoice'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::create_invoice');
    }
}
