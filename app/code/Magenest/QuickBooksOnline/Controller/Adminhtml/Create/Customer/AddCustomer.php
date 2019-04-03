<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Customer;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateCustomer;
use Magenest\QuickBooksOnline\Model\CustomerFactory;
use Magenest\QuickBooksOnline\Model\CustomerAddressFactory;
use Magenest\QuickBooksOnline\Model\Synchronization\Customer as SyncCustomer;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory as DefaultCustomer;

/**
 * Class Customer
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class AddCustomer extends AbstractCreateCustomer
{
    /**
     * @var SyncCustomer
     */
    protected $syncCustomer;

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
     * @var \Magenest\QuickBooksOnline\Model\Config
     */
    protected $config;

    /**
     * AddCustomer constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param SyncCustomer $syncCustomer
     * @param \Psr\Log\LoggerInterface $logger
     * @param DefaultCustomer $defaultCustomer
     * @param CustomerAddressFactory $customerAddressFactory
     * @param \Magenest\QuickBooksOnline\Model\Config $config
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        SyncCustomer $syncCustomer,
        \Psr\Log\LoggerInterface $logger,
        DefaultCustomer $defaultCustomer,
        CustomerAddressFactory $customerAddressFactory,
        \Magenest\QuickBooksOnline\Model\Config $config
    ) {
        parent::__construct($context, $customerFactory);
        $this->syncCustomer = $syncCustomer;
        $this->logger = $logger;
        $this->customer = $defaultCustomer;
        $this->address = $customerAddressFactory;
        $this->config = $config;
    }

    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $connect = $this->config->getConnected();
        if ($connect && $connect == 1) {
            $modelCustomer = $this->customerFactory->create()->getCollection();
            foreach ($modelCustomer as $customerData) {
                $customerData->delete();
            }
            $modelAddress = $this->address->create()->getCollection();
            foreach ($modelAddress as $addressData) {
                $addressData->delete();
            }
            $arrayStart = $this->countCustomer();
            foreach ($arrayStart as $start) {
                $collections = $this->syncCustomer->listCustomer($start);
                if (isset($collections['Customer'])) {
                    try {
                        /** @var \Magento\Customer\Model\Customer $customer */
                        foreach ($collections['Customer'] as $information) {
                            if (isset($information['PrimaryEmailAddr']['Address']) && !empty($information['PrimaryEmailAddr']['Address'])) {
                                $email = $information['PrimaryEmailAddr']['Address'];
                                if (isset($email) && !empty($email)
                                    && $this->checkCustomer($email) == 1
                                    && !empty($information['GivenName'])
                                    && !empty($information['FamilyName'])
                                ) {
                                    $this->addToCustomerTb($information);
                                }
                            }
                        }
                        $this->messageManager->addSuccessMessage('All customers added to the queue');
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage('Have an error when added to the queue');
                    }
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Not connect to QuickBooks Online'));
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @return array
     */
    public function countCustomer()
    {
        $count = $this->syncCustomer->getCountCustomer();
        $array = [1];
        if ($count > 100) {
            $check = round($count/100, 0);
            for ($i =1; $i <= $check; $i ++) {
                $value = $i *100;
                array_push($array, $value);
            }
        }

        return $array;
    }

    /**
     * @param $information
     */
    public function addToCustomerTb($information)
    {
        $model = $this->customerFactory->create();

        $data = [
            'qbo_id' => isset($information['Id']) ? $information['Id'] : null,
            'prefix' => isset($information['Title']) ? $information['Title'] : null,
            'firstname' => isset($information['GivenName']) ? $information['GivenName'] : null,
            'middlename' => isset($information['MiddleName']) ? $information['MiddleName'] : null,
            'lastname' => isset($information['FamilyName']) ? $information['FamilyName'] : null,
            'suffix' => isset($information['Suffix']) ? $information['Suffix'] : null,
            'email' => $information['PrimaryEmailAddr']['Address'],
            'vat_id' => isset($information['ResaleNum']) ? $information['ResaleNum'] : null,
            'website_id' => 1,
            'group_id' => 1,
            'gender' => 3,
            'sendemail_store_id' => 1
        ];

        $model->addData($data);
        $model->save();

        if (isset($information['BillAddr']) && !empty($information['BillAddr'])) {
            $billingId = $this->saveAddress($information, $model->getId());
            $model->setDefaultBilling($billingId)->save();
        }

        if (isset($information['ShipAddr']) && !empty($information['ShipAddr'])) {
            $shippingId = $this->saveAddress($information, $model->getId());
            $model->setDefaultShipping($shippingId)->save();
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function saveAddress($data, $id)
    {
        $model = $this->address->create();
        $array = [
            'enabled' => 1,
            'parent_id' => $id,
            'firstname' => isset($data['GivenName']) ? $data['GivenName'] : null,
            'middlename' => isset($data['MiddleName']) ? $data['MiddleName'] : null,
            'lastname' => isset($data['FamilyName']) ? $data['FamilyName'] : null,
            'street' => isset($data['BillAddr']['Line1']) ? $data['BillAddr']['Line1'] :  null,
            'city' => isset($data['BillAddr']['City']) ? $data['BillAddr']['Line1'] :  null,
            'region_id' => isset($data['BillAddr']['CountrySubDivisionCode']) ? $data['BillAddr']['CountrySubDivisionCode'] :  null,
            'postcode' => isset($data['BillAddr']['PostalCode']) ? $data['BillAddr']['PostalCode'] : null,
            'telephone' => isset($data['PrimaryPhone']['FreeFormNumber']) ? $data['PrimaryPhone']['FreeFormNumber'] : null,
        ];
        $array['country_id'] = null;
        if (isset($data['BillAddr']['Country'])) {
            $array['country_id'] = $data['BillAddr']['Country'];
        } elseif (isset($data['BillAddr']['CountrySubDivisionCode'])) {
            $array['country_id'] = 'US';
        }
        $model->addData($array)->save();

        return $model->getId();
    }

    /**
     * @param $email
     * @return int
     */
    public function checkCustomer($email)
    {
        $model = $this->customer->create()->getCollection()
            ->addFieldToFilter('email', $email)->getFirstItem();
        $check = 1;
        if (!empty($model->getData())> 0) {
            $check = 0;
        }

        return $check;
    }
}
