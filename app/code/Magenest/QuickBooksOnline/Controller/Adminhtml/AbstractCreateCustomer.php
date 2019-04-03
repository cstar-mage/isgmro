<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magenest\QuickBooksOnline\Model\CustomerFactory;

/**
 * Class AbstractCreateCustomer
 *
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml
 */
abstract class AbstractCreateCustomer extends Action
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * AbstractCreateCustomer constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
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
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::create_customer')
            ->addBreadcrumb(__('List Customer'), __('List Customer'));
        $resultPage->getConfig()->getTitle()->set(__('List Customer'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::create_customer');
    }
}
