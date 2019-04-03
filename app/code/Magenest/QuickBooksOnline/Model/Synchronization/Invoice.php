<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Framework\Exception\LocalizedException;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magenest\QuickBooksOnline\Model\Config;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Invoice using to sync Invoice
 *
 * @package Magenest\QuickBooksOnline\Model\Sync
 * @method InvoiceModel getModel()
 */
class Invoice extends Synchronization
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
     * @var InvoiceModel
     */
    protected $_invoice;

    /**
     * @var InvoiceModel
     */
    protected $_currentModel;

    /**
     * @var TaxFactory
     */
    protected $tax;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var PaymentMethods
     */
    protected $_paymentMethods;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var InvoiceModel\ItemFactory
     */
    protected $itemInvoice;

    /**
     * Invoice constructor.
     * @param Client $client
     * @param Log $log
     * @param InvoiceModel $invoice
     * @param Item $item
     * @param Customer $customer
     * @param TaxFactory $taxFactory
     * @param Config $config
     * @param \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Client $client,
        Log $log,
        InvoiceModel $invoice,
        Item $item,
        Customer $customer,
        TaxFactory $taxFactory,
        Config $config,
        \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Psr\Log\LoggerInterface $logger,
        OrderFactory $orderFactory
    ) {
        parent::__construct($client, $log);
        $this->_invoice  = $invoice;
        $this->_item     = $item;
        $this->_syncCustomer = $customer;
        $this->tax = $taxFactory;
        $this->type = 'invoice';
        $this->config = $config;
        $this->_paymentMethods = $paymentMethods;
        $this->_orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->product = $product;
        $this->itemInvoice = $invoiceItemFactory;
    }

    /**
     * Sync Invoice
     *
     * @param $incrementId
     * @return mixed
     * @throws LocalizedException
     */
    public function sync($incrementId)
    {
        $model = $this->_invoice->loadByIncrementId($incrementId);
        $orderIncrementId = $model->getOrder()->getIncrementId();
        $checkInvoice = $this->checkInvoice($orderIncrementId);
        if (!isset($checkInvoice['Id'])) {
            $this->addLog($incrementId, null, __('We can\'t find the Invoice Map #%1',$orderIncrementId));
        } else {
            try {
                if (!$model->getId()) {
                    throw new LocalizedException(__('We can\'t find the Invoice #%1', $incrementId));
                }
                $this->setModel($model);
                $this->prepareParams($checkInvoice['Id']);
                $params = $this->getParameter();
                $response = $this->sendRequest(\Zend_Http_Client::POST, 'payment', $params);
                $qboId = $response['Payment']['Id'];
                $this->addLog($incrementId, $checkInvoice['Id']);
                $this->parameter = [];
                return $qboId;
            } catch (LocalizedException $e) {
                $this->addLog($incrementId, null, $e->getMessage());
            }
        }

        $this->parameter = [];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function prepareParams($id)
    {
        $model = $this->getModel();
        $params = [
            'TxnDate'      => $model->getCreatedAt(),
            'CustomerRef'  => $this->prepareCustomerId(),
            'Line'         => $this->prepareLineInvoice($id),
            'TotalAmt'     => $model->getGrandTotal(),
        ];

        $this->setParameter($params);
        $this->preparePaymentMethod();

        return $this;
    }

    /**
     * get payment method
     */
    public function preparePaymentMethod()
    {
        $code = $this->getModel()->getOrder()->getPayment()
            ->getMethodInstance()
            ->getCode();
        $paymentMethod = $this->_paymentMethods->create()->load($code, 'payment_code');
        if ($paymentMethod->getId()) {
            $params['PaymentMethodRef'] = [
                'value' => $paymentMethod->getQboId(),
            ];
            $this->setParameter($params);
        }
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
     * Add Item to Order
     *
     * @return array
     */
    public function prepareLineInvoice($id)
    {
        $invoice[] = [
            'TxnId' => $id,
            'TxnType' => 'Invoice'
        ];
        $lines[] = [
            'Amount'      => $this->getModel()->getGrandTotal(),
            'LinkedTxn'   => $invoice,
            ];

        return $lines;
    }


    /**
     * Check invoice by Increment Id
     *
     * @param $id
     * @return array
     */
    protected function checkInvoice($id)
    {
        $prefix = $this->config->getPrefix('order');
        $name = $prefix.$id;
        $query = "SELECT Id, SyncToken FROM invoice WHERE DocNumber='{$name}'";

        return $this->query($query);
    }

    /**
     * @return array
     */
    public function getInvoice($name)
    {
        $query = "SELECT * FROM invoice WHERE DocNumber='{$name}'";

        return $this->query($query);
    }
}
