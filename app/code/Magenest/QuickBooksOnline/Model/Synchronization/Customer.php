<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Customer
 * @package Magenest\QuickBooksOnline\Model\Sync
 * @method CustomerModel getModel()
 */
class Customer extends Synchronization
{
    /**
     * @var CustomerModel
     */
    protected $_customerModel;

    /**
     * Customer constructor.
     *
     * @param Client $client
     * @param Log $log
     * @param CustomerModel $customer
     */
    public function __construct(
        Client $client,
        Log $log,
        CustomerModel $customer
    )
    {
        parent::__construct($client, $log);
        $this->_customerModel = $customer;
        $this->type = 'customer';
    }

    /**
     * Update or create new a record
     *
     * @param $id
     * @param bool $update
     * @return mixed
     * @throws LocalizedException
     */
    public function sync($id, $update = false)
    {
        $model = $this->_customerModel->load($id);
        if (!$model->getId()) {
            throw new LocalizedException(__('We can\'t find the customer have Id like %1', $id));
        }
        $email = $model->getEmail();
        $this->setModel($model);
        $customer = $this->checkCustomer($email);
        if (!empty($customer) && !$update) {
            return $customer['Id'];
        }

        $this->prepareParams();
        $params = array_replace_recursive($this->getParameter(), $customer);
        try {
            $response = $this->sendRequest(\Zend_Http_Client::POST, 'customer', $params);
            $qboId = $response['Customer']['Id'];
            $this->addLog($id, $qboId);

            return $qboId;
        } catch (LocalizedException $e) {
            $this->addLog($id, null, $e->getMessage());
        }
    }

    /**
     * Sync Guest Customer when place order
     *
     * @param \Magento\Sales\Model\Order\Address|\Magento\Sales\Api\Data\OrderAddressInterface $bill
     * @param \Magento\Sales\Model\Order\Address $ship
     * @return string
     * @throws \Exception
     */
    public function syncGuest($bill, $ship)
    {
        $firstName = trim($bill->getFirstname());
        $lastName = trim($bill->getLastname());
        $email = trim($bill->getEmail());
        $customer = $this->checkCustomer($email);
        if (!empty($customer)) {
            return $customer['Id'];
        }
        $suffix = time();
        $params = [
            'GivenName' => $firstName,
            'FamilyName' => $lastName,
            'Suffix' => $suffix,
            'PrimaryEmailAddr' => ['Address' => $bill->getEmail()],
            'PrimaryPhone' => ['FreeFormNumber' => $bill->getTelephone()],
            'CompanyName' => $bill->getCompany(),
        ];
        $params['BillAddr'] = $this->getAddress($bill);
        if ($ship !== null) {
            $params['ShipAddr'] = $this->getAddress($ship);
        }

        $response = $this->sendRequest(\Zend_Http_Client::POST, 'customer', $params);
        if (is_array($response)) {
            return $response['Customer']['Id'];
        } else {
            throw new LocalizedException(__('Can\'t sync guest to QuickBooks Online'));
        }
    }

    /**
     * @return $this
     */
    protected function prepareParams()
    {
        $model = $this->getModel();
        $params = [
            'GivenName' => trim($model->getFirstname()),
            'FamilyName' => trim($model->getLastname()),
            'Suffix' => $this->getModel()->getId(),
            'PrimaryEmailAddr' => ['Address' => $model->getEmail()]
        ];
        $this->setParameter($params);

        // set currency
        $this->setCurrencyParams();

        // set billing address
        $this->setBillingAddressParams();

        // set shipping address
        $this->setShippingAddressParams();

        return $this;
    }

    /**
     * @return $this
     */
    public function setCurrencyParams()
    {
        //TODO in the next version
        return $this;
    }

    /**
     * @return $this
     */
    public function setBillingAddressParams()
    {
        $billAddress = $this->getModel()->getDefaultBillingAddress();
        if ($billAddress) {
            $params = [
                'PrimaryPhone' => ['FreeFormNumber' => $billAddress->getTelephone()],
                'CompanyName' => $billAddress->getCompany(),
                'BillAddr' => $this->getAddress($billAddress)
            ];
            $this->setParameter($params);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setShippingAddressParams()
    {
        $shipAddress = $this->getModel()->getDefaultShippingAddress();
        if ($shipAddress) {
            $params = [
                'ShipAddr' => $this->getAddress($shipAddress)
            ];
            $this->setParameter($params);
        }

        return $this;
    }

    /**
     * Check Customer
     *
     * @param  $fistName
     * @param  $lastName
     * @return bool|array
     */
    public function checkCustomer($email)
    {
        $query = "SELECT Id, SyncToken FROM Customer";
        $query .= " WHERE PrimaryEmailAddr='{$email}'";
        return $this->query($query);
    }

    /**
     * Query Customer
     *
     * @param  $fistName
     * @param  $lastName
     * @return bool|array
     */
    public function getCustomer($params)
    {
        $query = "select * from Customer maxresults 1000";
        if (isset($params['type']) && $params['type'] == 'time_start') {
            $input = $params['from'];
            $query = "select * from Customer where MetaData.LastUpdatedTime >= '$input'";
        }
        if (isset($params['type']) && $params['type'] == 'time_around') {
            $from = $params['from'];
            $to = $params['to'];
            $query = "select * from Customer where MetaData.LastUpdatedTime >= '$from' and MetaData.LastUpdatedTime <= '$to'";
        }
        if (isset($params['type']) && $params['type'] == 'name') {
            $input = $params['input'];
            $query = "select * from Customer where FamilyName Like '$input'";
        }
        if (isset($params['type']) && $params['type'] == 'id') {
            $input = $params['input'];
            $query = "select * from Customer where  Id = '$input'";
        }
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }

    /**
     * @return mixed
     */
    public function getCountCustomer()
    {
        $query = "select COUNT(*) from Customer ";
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result['totalCount'];
    }

    /**
     * @return mixed
     */
    public function listCustomer($start)
    {
        $query = "select * from Customer startposition {$start} maxresults 100";
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }
}
