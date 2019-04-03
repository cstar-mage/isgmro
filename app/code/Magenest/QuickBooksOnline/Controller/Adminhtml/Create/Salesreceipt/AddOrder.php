<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateSalesReceipt;
use Magenest\QuickBooksOnline\Model\SalesReceiptFactory;
use Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory;
use Magenest\QuickBooksOnline\Model\SalesReceiptPaymentFactory;
use Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory;
use Magenest\QuickBooksOnline\Model\Synchronization\Customer as SyncCustomer;
use Magenest\QuickBooksOnline\Model\Synchronization\SalesReceipt as SyncOrder;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\OrderFactory;
use Magenest\QuickBooksOnline\Model\Config;

/**
 * Class AddOrder
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt
 */
class AddOrder extends AbstractCreateSalesReceipt
{
    /**
     * @var SyncOrder
     */
    protected $syncOrder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var SalesReceiptProductFactory
     */
    protected $salesReceiptProduct;

    /**
     * @var SalesReceiptPaymentFactory
     */
    protected $salesReceiptPayment;

    /**
     * @var SalesReceiptAddressFactory
     */
    protected $salesReceiptAddress;

    /**
     * @var SyncCustomer
     */
    protected $syncCustomer;

    /**
     * @var ProductFactory
     */
    protected $product;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * AddOrder constructor.
     * @param Context $context
     * @param SalesReceiptFactory $salesReceiptFactory
     * @param SyncOrder $syncOrder
     * @param SalesReceiptProductFactory $salesReceiptProductFactory
     * @param SalesReceiptPaymentFactory $salesReceiptPaymentFactory
     * @param SalesReceiptAddressFactory $salesReceiptAddressFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param SyncCustomer $syncCustomer
     * @param ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        SalesReceiptFactory $salesReceiptFactory,
        SyncOrder $syncOrder,
        SalesReceiptProductFactory $salesReceiptProductFactory,
        SalesReceiptPaymentFactory $salesReceiptPaymentFactory,
        SalesReceiptAddressFactory $salesReceiptAddressFactory,
        \Psr\Log\LoggerInterface $logger,
        SyncCustomer $syncCustomer,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        Config $config
    ) {
    
        parent::__construct($context, $salesReceiptFactory);
        $this->syncCustomer = $syncCustomer;
        $this->syncOrder = $syncOrder;
        $this->logger = $logger;
        $this->salesReceiptProduct = $salesReceiptProductFactory;
        $this->salesReceiptPayment = $salesReceiptPaymentFactory;
        $this->salesReceiptAddress = $salesReceiptAddressFactory;
        $this->product = $productFactory;
        $this->orderFactory = $orderFactory;
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
            $arrayStart = $this->countOrder();
            foreach ($arrayStart as $start) {
                $collections = $this->syncOrder->listOrder($start);
                if (isset($collections['SalesReceipt'])) {
                    try {
                        foreach ($collections['SalesReceipt'] as $information) {
                            $check = $this->checkOrder($information);
                            if ($check['check_customer'] + $check['check_product'] + $check['check_payment'] + $check['check_order'] = 4) {
                                $this->addToOrderTb($information, $check);
                            }
                        }
                        $this->messageManager->addSuccessMessage('All SalesReceipt added');
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage('Have an error when added');
                    }
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Not connect to QuickBooks Online'));
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * count number order
     * @return array
     */
    public function countOrder()
    {
        $count = $this->syncOrder->getCountOrder();
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
     * @param $check
     */
    public function addToOrderTb($information, $check)
    {
        $model = $this->salesReceiptFactory->create();
        $shippingAmount = 0;
        $line = $information['Line'];
        $tax = 0;
        foreach ($line as $lines) {
            if ($lines['DetailType'] = 'SubTotalLineDetail') {
                $subtotal = $lines['Amount'];
            }

            if ($lines['DetailType'] = 'SalesItemLineDetail' &&
                $lines['SalesItemLineDetail']['ItemRef']['value'] = 'SHIPPING_ITEM_ID'
            ) {
                $shippingAmount = $lines['Amount'];
            }
        }

        if (isset($information['TxnTaxDetail'])) {
            $tax = $information['TxnTaxDetail']['TotalTax'];
        }
        $data = [
            'qbo_id' => isset($information['Id']) ? $information['Id'] : null,
            'doc_number' => isset($information['DocNumber']) ? $information['DocNumber'] : null,
            'created_at' => isset($information['TxnDate']) ? $information['TxnDate'] : null,
            'status' => 'pending',
            'customer_id' => $check['customer_id'],
            'customer_name' => isset($information['CustomerRef']['name']) ? $information['CustomerRef']['name'] : null,
            'email' => isset($information['BillEmail']['Address']) ? $information['BillEmail']['Address'] : null,
            'shipping_amount' => $shippingAmount,
            'tax_amount' => $tax,
            'grand_total' => isset($information['TotalAmt']) ? strtolower($information['TotalAmt']) : null,
            'currency' => isset($information['CurrencyRef']['value']) ? strtolower($information['CurrencyRef']['value']) : 'USD',
            'payment_method' => $information['PaymentMethodRef']['value'],
            'payment_number' => isset($information['PaymentRefNum']) ? $information['PaymentRefNum'] : null,

        ];

        $model->addData($data);
        $model->save();
        $this->saveItem($information['Line'], $check['product'], $model->getId());
        if (isset($information['ShipAddr'])) {
            $shippingId = $this->saveAddress($information['ShipAddr']);
            $model->setShippingId($shippingId)->save();
        }
        if (isset($information['BillAddr'])) {
            $billingId = $this->saveAddress($information['BillAddr']);
            $model->setBillingId($billingId)->save();
        }
    }

    /**
     * @param $info
     * @param $data
     * @param $orderId
     */
    public function saveItem($info, $data, $orderId)
    {
        $i = 0;
        foreach ($info as $information) {
            if (isset($information['Id'])) {
                $array = [
                    'qborder_id' => $orderId,
                    'qbo_id' => $information['SalesItemLineDetail']['ItemRef']['value'],
                    'product_id' => $data[$i],
                    'name' => $information['SalesItemLineDetail']['ItemRef']['name'],
                    'sku' => $orderId,
                    'item_status' => 'ordered',
                    'price' => $information['SalesItemLineDetail']['UnitPrice'],
                    'qty' => $information['SalesItemLineDetail']['Qty'],
                ];
                $modelItem = $this->salesReceiptProduct->create();
                $modelItem->addData($array)->save();
            }
            $i++;
        }
    }

    /**
     * @param $info
     * @return mixed
     */
    public function saveAddress($info)
    {
        $model = $this->salesReceiptAddress->create();
        $array = [
            'qbo_id' => isset($info['Id']) ? $info['Id'] : null,
            'company' => isset($info['Company']) ? $info['Company'] : 'no company',
            'street' => isset($info['Line2']) ? $info['Line2'] : 'no street',
            'city' => isset($info['City']) ? $info['City'] : 'no city',
            'region_id' => isset($info['CountrySubDivisionCode']) ? $info['CountrySubDivisionCode'] : null,
            'postcode' => isset($info['PostalCode']) ? $info['PostalCode'] : '123456',
        ];
        $array['country_id'] = 'VN';
        if (isset($info['Country'])) {
            $array['country_id'] = $info['Country'];
        } elseif (isset($info['CountrySubDivisionCode'])) {
            $array['country_id'] = 'US';
        }
        $model->addData($array)->save();

        return $model->getId();
    }

    /**
     * @param $data
     * @return bool
     */
    public function checkOrder($data)
    {
        $checkOrderId = $this->checkOrderId(trim($data['DocNumber']));
        $checkCustomer = $this->checkCustomer($data['CustomerRef']);
        $checkProduct = $this->checkProduct($data['Line']);
        $checkPaymentMethod = $this->checkPaymentMethod($data['PaymentMethodRef']);
        $resultArray = array_merge($checkCustomer, $checkProduct, $checkPaymentMethod, $checkOrderId);

        return $resultArray;
    }

    /**
     * check order id
     */
    public function checkOrderId($docNumber)
    {
        $prefix = $this->config->getPrefix('salesreceipt');
        $incrementId = str_replace($prefix, '', $prefix);
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order) {
            return [
                'check_order' => 0,
                'order_increment_id' => $incrementId
            ];
        }

        return [
            'check_order' => 1,
            'order_increment_id' => '111111111'
        ];
    }

    /**
     * check isset customer
     * @param $customer
     * @return bool
     */
    public function checkCustomer($customer)
    {
        $id = $customer['value'];
        $params = [
            'type' => 'id',
            'input' => $id
        ];
        $customerId = 0;
        $checkCustomer = 0;
        $check = $this->syncCustomer->getCustomer($params);
        $email = $check['Customer'][0]['PrimaryEmailAddr']['Address'];
        $customerModel = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Customer')->getCollection()
            ->addFieldToFilter('email', $email)->getFirstItem();

        if ($customerModel->getId() > 0) {
            $checkCustomer = 1;
            $customerId = $customerModel->getId();
        }

        return [
            'check_customer' => $checkCustomer,
            'customer_id' => $customerId
        ];
    }

    /**
     * check isset product
     * @param $product
     * @return bool
     */
    public function checkProduct($product)
    {
        $checkProduct = 1;
        $productArray = [];
        $array = [];
        foreach ($product as $products) {
            if ($products['DetailType'] == 'SalesItemLineDetail' && isset($products['SalesItemLineDetail']['UnitPrice'])) {
                $name = trim($products['SalesItemLineDetail']['ItemRef']['name']);
                $model = $this->product->create()->getCollection()->addFieldToFilter('name', $name)->getFirstItem();
                if ($model->getId() > 0) {
                    $result = ['true'];
                    $productId = [$model->getId()];
                    $productArray = array_merge($productArray, $productId);
                } else {
                    $result = ['false'];
                }
                $array = array_merge($array, $result);
            }
        }

        if (in_array('false', $array)) {
            $checkProduct = 0;
        }

        return [
            'check_product' => $checkProduct,
            'product' => $productArray,
        ];
    }

    /**
     * check payment method
     * @param $payment
     * @return bool
     */
    public function checkPaymentMethod($payment)
    {
        $value = $payment['value'];
        $paymentMethod = 0;
        $checkPayment = 0;
        $model = \Magento\Framework\App\ObjectManager::getInstance()->create('Magenest\QuickBooksOnline\Model\PaymentMethods');
        if (!empty($model->load($value, 'qbo_id')->getData())) {
            $checkPayment = 1;
            $paymentMethod = $payment['name'];
        }

        return [
            'check_payment' => $checkPayment,
            'payment_method' => $paymentMethod,
        ];
    }
}
