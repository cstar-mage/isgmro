<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\SalesReceipt;

/**
 * Class View
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\SalesReceipt
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'create/salesreceipt/sales.phtml';

    /**
     * @var \Magenest\QuickBooksOnline\Model\SalesReceiptFactory
     */
    protected $salesReceipt;

    /**
     * @var \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory
     */
    protected $salesProduct;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $address;

    /**
     * @var \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory
     */
    protected $salesAddress;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magenest\QuickBooksOnline\Model\SalesReceiptFactory $salesReceiptFactory
     * @param \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory $salesReceiptProductFactory
     * @param \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory $salesReceiptAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magenest\QuickBooksOnline\Model\SalesReceiptFactory $salesReceiptFactory,
        \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory $salesReceiptProductFactory,
        \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory $salesReceiptAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
         parent::__construct($context, $data);
         $this->salesReceipt = $salesReceiptFactory;
         $this->salesProduct = $salesReceiptProductFactory;
         $this->customer = $customerFactory;
         $this->address = $addressFactory;
         $this->currency = $customerFactory;
         $this->salesAddress = $salesReceiptAddressFactory;
    }

    /**
     * Get qboder id
     * @return mixed
     */
    public function getSalesId()
    {
        $id = $this->_request->getParam('id');

        return $id;
    }

    /**
     * get data order
     * @return $this
     */
    public function getOrderData()
    {
        $model = $this->salesReceipt->create()->load($this->getSalesId());

        return $model;
    }


    /**
     * get data product
     * @return $this
     */
    public function getProductData()
    {
        $model = $this->salesProduct->create()->getCollection()->addFieldToFilter('qborder_id', $this->getSalesId())->getData();

        return $model;
    }


    /**
     * get data customer
     * @return $this
     */
    public function getCustomerInformation()
    {
        $model = $this->customer->create()->load($this->getOrderData()->getCustomerId());

        return $model;
    }

    /**
     * get data address
     * @return array
     */
    public function getAddress()
    {
        $order = $this->getOrderData();
        $data = [];
        $billingId = $order->getBillingId();
        if (isset($billingId)) {
            $model = $this->salesAddress->create()->load($billingId);
            $data['billing'] = [
                'id' => $model->getId(),
                'name' => $order->getCustomerName(),
                'company' =>  $model->getCompany(),
                'street' =>  $model->getStreet(),
                'city' =>  $model->getCity(),
                'country' =>  $model->getCountryId(),
                'region' =>  $model->getRegionId(),
                'postcode' =>  $model->getPostcode()
            ];
        }
        $shippingId = $order->getShippingId();
        if (isset($shippingId)) {
            $model = $this->salesAddress->create()->load($shippingId);
            $data['shipping'] = [
                'id' => $model->getId(),
                'name' => $order->getCustomerName(),
                'company' =>  $model->getCompany(),
                'street' =>  $model->getStreet(),
                'city' =>  $model->getCity(),
                'country' =>  $model->getCountryId(),
                'region' =>  $model->getRegionId(),
                'postcode' =>  $model->getPostcode(),
            ];
        }

        return $data;
    }

    /**get url customer
     * @return string
     */
    public function getUrlCustomer()
    {
        return $this->getUrl('customer/index/edit', ['id'=> $this->getOrderData()->getCustomerId()]);
    }

    /**
     * get currnecy symbol
     * @return mixed
     */
    public function getCurrencySymbol()
    {
        $currency = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->getOrderData()->getCurrency());
        $currencySymbol = $currency->getCurrencySymbol();

        return $currencySymbol;
    }
}
