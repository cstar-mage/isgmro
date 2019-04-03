<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateCustomer;
use Magenest\QuickBooksOnline\Model\CustomerFactory;
use Magenest\QuickBooksOnline\Model\CustomerAddressFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory as DefaultCustomer;

/**
 * Class Create
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Create extends AbstractCreateCustomer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var DefaultCustomer
     */
    protected $customer;

    /**
     * @var CustomerAddressFactory
     */
    protected $address;

    /**
     * Create constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param DefaultCustomer $defaultCustomer
     * @param CustomerAddressFactory $customerAddressFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger,
        DefaultCustomer $defaultCustomer,
        CustomerAddressFactory $customerAddressFactory
    ) {
        parent::__construct($context, $customerFactory);
        $this->logger = $logger;
        $this->customer = $defaultCustomer;
        $this->address = $customerAddressFactory;
    }

    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $queueCustomer= $this->customerFactory->create()->getCollection();

        try {
            /** @var \Magento\Customer\Model\Customer $customer */
            foreach ($queueCustomer as $information) {
                $this->createNewCustomerTb($information);
                $information->delete();
            }
            $this->messageManager->addSuccessMessage('All customers have added');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Have an error when added to Customer');
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @param $information
     */
    public function createNewCustomerTb($information)
    {
        $idBilling = null;
        $isShipping = null;
        /** @var \Magento\Customer\Model\Customer $model */
        $modelCustomer = $this->customer->create();

        /** @var \Magenest\QuickBooksOnline\Model\Customer $information */
        $params = $information->getData();
        unset($params['default_billing']);
        unset($params['default_shipping']);
        unset($params['customer_id']);
        unset($params['qbo_id']);
        unset($params['sendemail_store_id']);
        $params['password'] = 'quickbooks1234';

        $modelCustomer->addData($params)->save();

        if (!empty($information->getDefaultBilling())) {
            $idBilling = $this->saveAddress($information->getDefaultBilling(), $modelCustomer->getId());
            if ($idBilling) {
                $modelCustomer->setDefaultBilling($idBilling)->save();
            }
        }
        if (!empty($information->getDefaultShipping())) {
            $isShipping = $this->saveAddress($information->getDefaultShipping(), $modelCustomer->getId());
            if ($isShipping) {
                $modelCustomer->setDefaultShipping($isShipping)->save();
            }
        }

//        $modelCustomer->sendNewAccountEmail();
    }

    /**
     * @param $addrId
     * @param $id
     * @return mixed
     */
    public function saveAddress($addrId, $id)
    {
        $model = $this->address->create()->load($addrId)->getData();
        $model['parent_id'] = $id;
        unset($model['entity_id']);
        unset($model['enabled']);
        if (!empty($model['country_id'])) {
            $addresss = $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
            $address = $addresss->create();
            $address->addData($model);
            $address->save();
            return $address->getId();
        }

        return false;
    }
}
