<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Sales\Model\Order\Creditmemo as CreditmemoModel;
use Magento\Framework\Exception\LocalizedException;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magento\Sales\Model\OrderFactory;
use Magenest\QuickBooksOnline\Model\Config;

/**
 * Class Creditmemo
 * @package Magenest\QuickBooksOnline\Model\Synchronization
 * @method CreditMemoModel getModel()
 */
class Creditmemo extends Synchronization
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var Customer
     */
    protected $_syncCustomer;

    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var CreditmemoModel
     */
    protected $_creditmemo;

    /**
     * @var PaymentMethods
     */
    protected $_paymentMethods;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var TaxFactory
     */
    protected $tax;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CreditmemoModel\ItemFactory
     */
    protected $itemCreditmemo;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * Creditmemo constructor.
     * @param Client $client
     * @param Log $log
     * @param CreditmemoModel $creditmemo
     * @param Item $item
     * @param Customer $customer
     * @param \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Psr\Log\LoggerInterface $logger
     * @param TaxFactory $taxFactory
     * @param OrderFactory $orderFactory
     * @param Config $config
     */
    public function __construct(
        Client $client,
        Log $log,
        CreditmemoModel $creditmemo,
        Item $item,
        Customer $customer,
        \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods,
        \Magento\Catalog\Model\ProductFactory $product,
        \Psr\Log\LoggerInterface $logger,
        TaxFactory $taxFactory,
        OrderFactory $orderFactory,
        Config $config
    ) {
        parent::__construct($client, $log);
        $this->_creditmemo  = $creditmemo;
        $this->_item     = $item;
        $this->_syncCustomer = $customer;
        $this->_paymentMethods = $paymentMethods;
        $this->tax = $taxFactory;
        $this->type = 'creditmemo';
        $this->logger = $logger;
        $this->_orderFactory = $orderFactory;
        $this->config = $config;
        $this->product = $product;
    }

    /**
     * Sync Invoice
     *
     * @param $id
     * @return mixed
     * @throws LocalizedException
     */
    public function sync($id)
    {
        $model = $this->_creditmemo->load((string)$id);
        $checkCredit = $this->checkCreditmemo($id);
        if (isset($checkCredit['Id'])) {
            $this->addLog($id, $checkCredit['Id'], 'This Creditmemo have existed .If you want resync , you need to change prefix creditmemo on configuration.');
        } else {
            try {
                if (!$model->getId()) {
                    throw new LocalizedException(__('We can\'t find the Creditmemo #%1', $id));
                }
                $this->setModel($model);
                $this->prepareParams();
                $params = $this->getParameter();
                $response = $this->sendRequest(\Zend_Http_Client::POST, 'creditmemo', $params);
                $qboId = $response['CreditMemo']['Id'];
                $this->addLog($id, $qboId);
                $this->parameter = [];
                return $qboId;
            } catch (LocalizedException $e) {
                $this->addLog($id, null, $e->getMessage());
            }
        }

        $this->parameter = [];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function prepareParams()
    {
        $model = $this->getModel();
        $prefix = $this->config->getPrefix('creditmemos');
        $params = [
            'DocNumber'    => $prefix.$model->getIncrementId(),
            'TxnDate'      => $model->getCreatedAt(),
            'TxnTaxDetail' => ['TotalTax' => $model->getTaxAmount()],
            'CustomerRef'  => $this->prepareCustomerId(),
            'Line'         => $this->prepareLineItems(),
            'TotalAmt'     => $model->getGrandTotal(),
            'BillEmail'    => ['Address' => $model->getOrder()->getCustomerEmail()],
        ];

        $this->setParameter($params);
        // st Tax
        if ($this->config->getCountry() != 'UK' && $model->getTaxAmount() > 0) {
            $this->prepareTax();
        }

        //set billing address
        $this->prepareBillingAddress();

        //set shipping address
        $this->prepareShippingAddress();

        //set payment method
        $this->preparePaymentMethod();

        return $this;
    }

    /**
     * Create Tax
     */
    public function prepareTax()
    {
        $params['TxnTaxDetail'] = [
            'TotalTax' => $this->getModel()->getTaxAmount(),
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function prepareCustomerId()
    {
        try {
            $model = $this->getModel();
            $customerId = $model->getOrder()->getCustomerId();
            if ($customerId) {
                $cusRef = $this->_syncCustomer->sync($customerId);
            } else {
                $cusRef = $this->_syncCustomer->syncGuest(
                    $model->getBillingAddress(),
                    $model->getShippingAddress()
                );
            }

            return ['value' => $cusRef];
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Can\'t sync customer on Invoice to QBO')
            );
        }
    }

    /**
     * @return void
     */
    public function prepareBillingAddress()
    {
        $billAddress = $this->getModel()->getBillingAddress();
        if ($billAddress !== null) {
            $params['BillAddr'] = $this->getAddress($billAddress);
            $this->setParameter($params);
        }
    }

    /**
     * @return void
     */
    public function prepareShippingAddress()
    {
        $shippingAddress = $this->getModel()->getShippingAddress();
        if ($shippingAddress !== null) {
            $params['ShipAddr'] = $this->getAddress($shippingAddress);
            $this->setParameter($params);
        }
    }

    /**
     * set payment method
     */
    public function preparePaymentMethod()
    {
        $orderData = $this->_orderFactory->create()->load($this->getModel()->getOrderId());
        $code = $orderData->getPayment()->getMethodInstance()->getCode();


        $paymentMethod = $this->_paymentMethods->create()->load($code, 'payment_code');

        if ($paymentMethod->getId()) {
            $params['PaymentMethodRef'] = [
                'value' => $paymentMethod->getQboId(),
                'name'  => $paymentMethod->getTitle()
            ];
            $this->setParameter($params);
        }
    }

    /**
     * Add Item to Order
     *
     * @return array
     */
    public function prepareLineItems()
    {
        $model = $this->_orderFactory->create()->load($this->getModel()->getOrderId());
        $i     = 1;
        $lines = [];
//        $total = 0;
//        $itemId = 0;
//        $price = 0;
//        $qty = 0;
//        $tax = null;

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($model->getAllItems() as $item) {
            $productType = $item->getProductType();
            if ($productType == 'configurable') {
                foreach ($item->getChildrenItems() as $test) {
                    $productId = $test->getProductId();
                    $price     = $item->getPrice();
                    $qty     = $test->getQtyOrdered();
                    $total   = $item->getRowTotal();
                    $itemId  = $this->_item->sync($productId);
                    $tax = $item->getTaxAmount() > 0 ? true : false;
                }
            } else {
                $productId = $item->getProductId();
                $price     = $item->getPrice();
                $qty     = $item->getQtyOrdered();
                $total   = $item->getRowTotal();
                $tax = $item->getTaxAmount() > 0 ? true : false;
                $itemId  = $this->_item->sync($productId);
            }
            if ($this->config->getCountry() != 'UK') {
                $lines[] = [
                    'LineNum'             => $i,
                    'Amount'              => $total,
                    'DetailType'          => 'SalesItemLineDetail',
                    'SalesItemLineDetail' => [
                        'ItemRef'    => ['value' => $itemId],
                        'UnitPrice'  => $price,
                        'Qty'        => $qty,
                        'TaxCodeRef' => ['value' => $tax ? 'TAX' : 'NON'],
                    ],
                ];
            } else {
                $lines[] = [
                    'LineNum'             => $i,
                    'Amount'              => $total,
                    'DetailType'          => 'SalesItemLineDetail',
                    'SalesItemLineDetail' => [
                        'ItemRef'    => ['value' => $itemId],
                        'UnitPrice'  => $price,
                        'Qty'        => $qty,
                        'TaxCodeRef' => ['value' => $tax ? $this->prepareTaxCodeRef($item->getItemId()) : $this->getTaxFree()]
                    ],
                ];
            }

            $i++;
        }

        //build shipping fee
        $lines[] = $this->prepareLineShippingFee();

        //build discount fee
        $lines[] = $this->prepareLineDiscountAmount();

        return $lines;
    }

    /**
     * Create Tax
     */
    public function prepareTaxCodeRef($itemId)
    {
        $taxCode = 0 ;
        /** @var \Magento\Sales\Model\Order\Tax\Item $modelTaxItem */
        $modelTaxItem = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Tax\Item')->load($itemId, 'item_id');
        if ($modelTaxItem) {
            $taxId = $modelTaxItem->getTaxId();
            $modelTax = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Tax')->load($taxId);

            if ($modelTax && !empty($modelTax->getData())) {
                $taxCode = $modelTax->getCode();
            }
            $tax = $this->tax->create()->load($taxCode, 'tax_code');
            if ($tax->getQboId() && $tax->getQboId() > 0) {
                $taxCodeId = $tax->getQboId();

                return $taxCodeId;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getTaxFree()
    {
        $modelTax = $this->tax->create()->load('tax_uk_zero', 'tax_code');
        if ($modelTax) {
            return $modelTax->getQboId();
        }

        return false;
    }

    /**
     * @param bool $hasTax
     * @return array
     */
    public function getTaxCodeRef($hasTax)
    {
        return ['value' => $hasTax ? 'TAX' : 'NON'];
    }

    /**
     * @return array
     */
    public function prepareLineShippingFee()
    {
        
        $shippingAmount = $this->getModel()->getShippingInclTax();
        if ($this->config->getCountry() == 'UK') {
            $lines = [
                'Amount'              => $shippingAmount ? $shippingAmount : 0,
                'DetailType'          => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef'    => ['value' => 'SHIPPING_ITEM_ID'],
                    'TaxCodeRef' => ['value' => $this->config->getTaxShipping()],
                ],
            ];
        } else {
            $lines = [
                'Amount'              => $shippingAmount ? $shippingAmount : 0,
                'DetailType'          => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef'    => ['value' => 'SHIPPING_ITEM_ID'],
                ],
            ];
        }

        return $lines;
    }

    /**
     * @return array
     */
    public function prepareLineDiscountAmount()
    {
        $discountAmount = $this->getModel()->getDiscountAmount();
        $lines = [
            'Amount'             => $discountAmount ?  -1 * $discountAmount : 0,
            'DetailType'         => 'DiscountLineDetail',
            'DiscountLineDetail' => [
                'PercentBased'       => false,
            ]
        ];

        return $lines;
    }

    /**
     * Check creditmemo by Increment Id
     *
     * @param $id
     * @return array
     */
    protected function checkCreditmemo($id)
    {
        $prefix = $this->config->getPrefix('creditmemos');
        $name = $prefix.$id;
        $query = "SELECT Id, SyncToken FROM CreditMemo WHERE DocNumber='{$name}'";

        return $this->query($query);
    }
}
