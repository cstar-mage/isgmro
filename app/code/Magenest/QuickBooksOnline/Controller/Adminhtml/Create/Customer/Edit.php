<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateCustomer as AbstractCreateCustomer;
use Magenest\QuickBooksOnline\Model\CustomerFactory;
use Magenest\QuickBooksOnline\Model\CustomerAddressFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer
 */
class Edit extends AbstractCreateCustomer
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
     * @var CustomerAddressFactory
     */
    protected $address;

    /**
     * Edit constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param CustomerAddressFactory $customerAddressFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        CustomerAddressFactory $customerAddressFactory
    ) {
        parent::__construct($context, $customerFactory);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->address = $customerAddressFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_QuickBooksOnline::create_customer')
            ->addBreadcrumb(__('View Customer'), __('View Customer'));

        return $resultPage;
    }

    /**
     * @return $this|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magenest\QuickBooksOnline\Model\Customer');
        $billing = '';
        $shipping = '';
        // 2. Initial checking
        if ($id) {
            $model->load($id);

            if (!empty($model->getDefaultBilling())) {
                $billing = $this->address->create()->load($model->getDefaultBilling());
            }
            if (!empty($model->getDefaultShipping())) {
                $shipping = $this->address->create()->load($model->getDefaultShipping());
            }

            if (!$model->getId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('customer', $model);
        $this->_coreRegistry->register('billing', $billing);
        $this->_coreRegistry->register('shipping', $shipping);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('View Customer'));

        return $resultPage;
    }
}
