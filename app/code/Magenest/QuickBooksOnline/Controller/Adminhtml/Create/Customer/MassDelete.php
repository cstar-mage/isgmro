<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateCustomer;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\QuickBooksOnline\Model\CustomerFactory;
use Magenest\QuickBooksOnline\Model\CustomerAddressFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer
 */
class MassDelete extends AbstractCreateCustomer
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CustomerAddressFactory
     */
    protected $address;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CustomerFactory $customerFactory
     * @param CustomerAddressFactory $customerAddressFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CustomerFactory $customerFactory,
        CustomerAddressFactory $customerAddressFactory
    ) {
        parent::__construct($context, $customerFactory);
        $this->filter = $filter;
        $this->address = $customerAddressFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $customerCollection = $this->customerFactory->create()->getCollection();
        $address = $this->address->create();
        $collection = $this->filter->getCollection($customerCollection);
        $i = 0;
        /** @var \Magenest\QuickBooksOnline\Model\Customer $customer */
        foreach ($collection->getItems() as $customer) {
            $billingId = $customer->getDefaultBilling();
            if ($billingId != null) {
                $address->load($billingId)->delete();
            }
            $shippingId = $customer->getDefaultShipping();
            if ($shippingId != null) {
                $address->load($shippingId)->delete();
            }
            $customer->delete();
            $i++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $i)
        );

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        
        return $resultRedirect;
    }
}
