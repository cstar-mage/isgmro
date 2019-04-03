<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateSalesReceipt;
use Magenest\QuickBooksOnline\Model\SalesReceiptFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt
 */
class Edit extends AbstractCreateSalesReceipt
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param SalesReceiptFactory $salesReceiptFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        SalesReceiptFactory $salesReceiptFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context, $salesReceiptFactory);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::create_salesreceipt')
            ->addBreadcrumb(__('View SalesReceipt'), __('View SalesReceipt'));

        return $resultPage;
    }

    /**
     * @return $this|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('View SalesReceipt'));

        return $resultPage;
    }
}
