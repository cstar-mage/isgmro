<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magenest\QuickBooksOnline\Model\Authenticate;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractUpdateCustomer
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml
 */
abstract class AbstractUpdateCustomer extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Authenticate
     */
    protected $authenticate;

    /**
     * AbstractConnection constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Authenticate $authenticate
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Authenticate $authenticate
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->authenticate = $authenticate;
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
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::update_customer')
            ->addBreadcrumb(__('Update Customer'), __('Update Customer'));
        $resultPage->getConfig()->getTitle()->set(__('Update Customer'));

        return $resultPage;
    }


    /**
     * Check Acl
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_QuickBooksOnline::update_customer');
    }
}
