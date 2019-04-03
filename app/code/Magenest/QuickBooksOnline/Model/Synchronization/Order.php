<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Sales\Model\OrderFactory;
use Magento\Config\Model\Config as ConfigModel;
use Magento\Framework\Exception\LocalizedException;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magenest\QuickBooksOnline\Model\PaymentMethodsFactory;
use Magenest\QuickBooksOnline\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Class Order
 * @package Magenest\QuickBooksOnline\Model\Synchronization
 */
class Order extends Synchronization
{
    /**
     * @var Customer
     */
    protected $_syncCustomer;

    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var PaymentMethods
     */
    protected $_paymentMethods;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var TaxFactory
     */
    protected $tax;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Order constructor.
     * @param Client $client
     * @param Log $log
     * @param OrderFactory $orderFactory
     * @param PaymentMethodsFactory $paymentMethods
     * @param Item $item
     * @param Customer $customer
     * @param TaxFactory $taxFactory
     * @param Config $config
     * @param LoggerInterface $logger
     *
     */
    public function __construct(
        Client $client,
        Log $log,
        OrderFactory $orderFactory,
        PaymentMethodsFactory $paymentMethods,
        Item $item,
        Customer $customer,
        TaxFactory $taxFactory,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($client, $log);
        $this->_orderFactory = $orderFactory;
        $this->_item         = $item;
        $this->_syncCustomer = $customer;
        $this->_paymentMethods = $paymentMethods;
        $this->type = 'order';
        $this->tax = $taxFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Sync Sales Order to Sales Receipt
     *
     * @param $incrementId
     * @return mixed
     * @throws \Exception
     */
    public function sync($incrementId)
    {
        $model = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        /** @var \Magento\Sales\Model\Order\Item $item */
        $checkOrder =  $this->checkOrder($incrementId);
        if (isset($checkOrder['Id'])) {
            $this->addLog($incrementId, $checkOrder['Id'], 'This Order have existed.');
        } else {
            try {
                if (!$model->getId()) {
                    throw new LocalizedException(__('We can\'t find the Order #%1', $incrementId));
                }
                $this->setModel($model);
                $this->prepareParams();
                $params = array_replace_recursive(
                    $this->getParameter(),
                    $this->checkOrder($incrementId)
                );
                $response = $this->sendRequest(\Zend_Http_Client::POST, 'invoice', $params);
                $qboId = $response['Invoice']['Id'];
                $this->addLog($incrementId, $qboId);
                $this->parameter = [];
                return $qboId;
            } catch (LocalizedException $e) {
                $this->addLog($incrementId, null, $e->getMessage());
            }
        }

        $this->parameter = [];
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareParams()
    {
        $model = $this->getModel();
        $prefix = $this->config->getPrefix('order');
        $params = [
            //'DocNumber'    => '',//$prefix.$model->getIncrementId(),
            'TxnDate'      => $model->getCreatedAt(),
            'CustomerRef'  => $this->prepareCustomerId(),
            'Line'         => $this->prepareLineItems(),
            'TotalAmt'     => $model->getGrandTotal(),
            'BillEmail'    => ['Address' => $model->getCustomerEmail()],
        ];
        $commentCode = $model->getCommentCode();
        $payment = $model->getPayment();
        if(!empty($commentCode)){
            $params['CustomerMemo'] = array('value' => $commentCode);
        }
        if($payment){
            $params['CustomField'][] = array('Name' => 'Purchase Order Number', 'DefinitionId' => 1, 'Type' => 'StringType', 'StringValue' => $payment->getPoNumber());
        }
        $this->setParameter($params);
        // st Tax
        if ($this->config->getCountry() != 'UK' && $model->getTaxAmount() > 0) {
            $this->prepareTax();
        }

        //set billing address
        $this->prepareBillingAddress();

        //set shipping address
        $this->prepareShippingAddress();

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
            $customerId = $model->getCustomerId();
            if ($customerId) {
                $cusRef = $this->_syncCustomer->sync($customerId, true);
            } else {
                $cusRef = $this->_syncCustomer->syncGuest(
                    $model->getBillingAddress(),
                    $model->getShippingAddress()
                );
            }

            return ['value' => $cusRef];
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Can\'t sync customer on Order to QBO')
            );
        }
    }

    /**
     * get Billing
     */
    public function prepareBillingAddress()
    {
        /** @var \Magento\Sales\Model\Order\Address $billAddress */
        $billAddress = $this->getModel()->getBillingAddress();
        if ($billAddress !== null) {
            $params['BillAddr'] = $this->getAddress($billAddress);
            $this->setParameter($params);
        }
    }

    /**
     * get shipping
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
     * Add Item to Order
     *
     * @return array
     */
    public function prepareLineItems()
    {
        $i     = 1;
        $lines = [];
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
        foreach ($this->getModel()->getItems() as $item) {
            $productType = $item->getProductType();
            $product = $item->getProduct();
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
                    'Description'         => $product->getName(),
                    'DetailType'          => 'SalesItemLineDetail',
                    'SalesItemLineDetail' => [
                        'ItemRef'    => ['value' => $itemId, 'name' => $product->getSku()],
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
                        'ItemRef'    => ['value' => $itemId, 'name' => $product->getSku()],
                        'UnitPrice'  => $price,
                        'Qty'        => $qty,
                        'TaxCodeRef' => ['value' => $tax ? $this->prepareTaxCodeRef($item->getItemId()) : $this->getTaxFree()]
                    ],
                ];
            }
$logger->info($itemId);
            $i++;
        }

        // set shipping fee
        if ($this->prepareLineShippingFee())
            $lines[] = $this->prepareLineShippingFee();

        // set discount
        $lines[] = $this->prepareLineDiscountAmount();


            $logger->info($lines);
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
     * @return array
     */
    protected function prepareLineShippingFee()
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
    protected function prepareLineDiscountAmount()
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
     * Check SalesReceipt by Increment Id
     *
     * @param $id
     * @return array
     */
    protected function checkOrder($id)
    {
        $prefix = $this->config->getPrefix('order');
        $name = $prefix.$id;
        $query = "SELECT Id, SyncToken FROM invoice WHERE DocNumber='{$name}'";

        return $this->query($query);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getOrder($params)
    {
        $query = "SELECT * FROM salesreceipt";
        if (isset($params['type']) && $params['type'] == 'id') {
            $input = $params['input'];
            $query = "select * from salesreceipt where  Id = '$input'";
        }
        $path = 'query?query='.rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }

    /**
     * count order
     *
     * @return mixed
     */
    public function getCountOrder()
    {
        $query = "select COUNT(*) from salesreceipt ";
        $path = 'query?query='.rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result['totalCount'];
    }

    /**
     * list all order when creat new
     * @return mixed
     */
    public function listOrder($start)
    {
        $query = "select * from salesreceipt startposition {$start} maxresults 10";
        $path = 'query?query='.rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }

    /**
     * @param $incrementId
     * @return int
     */
    public function getSaleReceiptDelete($incrementId)
    {
        $prefix = $this->config->getPrefix('order');
        $docNumber = $prefix.$incrementId;
        $query = "select * from estimate where DocNumber= '$docNumber' ";
        $path = 'query?query='.rawurlencode($query);

        $response = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $data = '';
        if (isset($response['QueryResponse']['Estimate'])) {
            $data = [
                'id' => $response['QueryResponse']['Estimate'][0]['Id'],
                'email' => $response['QueryResponse']['Estimate'][0]['BillEmail']['Address']
            ];
        }

        return $data;
    }

    /**
     * @param $id
     * @param $incrementId
     * @return mixed|string
     */
    public function cancelSaleReceipt($id, $incrementId)
    {
        $prefix = $this->config->getPrefix('order');
        $params = [
            'Id' => $id,
            'SyncToken' => 1,
            'DocNumber' => $prefix.$incrementId
        ];
        $response = $this->sendRequest(\Zend_Http_Client::POST, 'estimate?operation=delete', $params);
        return $response;
    }
}
