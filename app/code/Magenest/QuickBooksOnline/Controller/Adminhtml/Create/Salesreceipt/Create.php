<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt;

use Magenest\QuickBooksOnline\Model\SalesReceiptFactory;
use Magento\Backend\App\Action\Context;
use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateSalesReceipt;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Create
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt
 */
class Create extends AbstractCreateSalesReceipt
{
    /**
     * @var \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory
     */
    protected $product;
    /**
     * @var \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory
     */
    protected $address;

    /**
     * @var \Magenest\QuickBooksOnline\Helper\QuoteOrder
     */
    protected $createOrder;

    protected $logger;

    /**
     * Create constructor.
     * @param Context $context
     * @param SalesReceiptFactory $salesReceiptFactory
     * @param \Magenest\QuickBooksOnline\Helper\QuoteOrder $quoteOrder
     * @param \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory $salesReceiptProductFactory
     * @param \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory $salesReceiptAddressFactory
     */
    public function __construct(
        Context $context,
        SalesReceiptFactory $salesReceiptFactory,
        \Magenest\QuickBooksOnline\Helper\QuoteOrder $quoteOrder,
        \Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory $salesReceiptProductFactory,
        \Magenest\QuickBooksOnline\Model\SalesReceiptAddressFactory $salesReceiptAddressFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $salesReceiptFactory);
        $this->product = $salesReceiptProductFactory;
        $this->address = $salesReceiptAddressFactory;
        $this->createOrder = $quoteOrder;
        $this->logger = $logger;
    }

    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magenest\QuickBooksOnline\Model\SalesReceipt $salesReceipt */
        $salesReceipt = $this->salesReceiptFactory->create()->getCollection();

        /** @var \Magenest\QuickBooksOnline\Model\SalesReceipt $order */

        foreach ($salesReceipt as $order) {
            try {
                $tempOrder=[
                    'store_id' => 1,
                    'website_id' => 1,
                    'currency_id'  => $order->getCurrency(),
                    'email'        => $order->getEmail(), //buyer email id
                    'shipping_amount'    => $order->getShippingAmount(),
                    'tax'        => $order->getTaxAmount(),
                ];
                if (!empty($order->getShippingId())) {
                    $tempOrder['shipping_address'] = $this->getAddress($order, $order->getShippingId());
                }
                if (!empty($order->getBillingId())) {
                    $tempOrder['billing_address'] = $this->getAddress($order, $order->getBillingId());
                }
                $tempOrder['items'] = $this->getItem($order->getId());
                $this->createOrder->createOrder($tempOrder);
                $this->messageManager->addSuccessMessage('All Orders have created');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }

    /**
     * @param $order
     * @param $shippingId
     * @return array
     */
    public function getAddress($order, $id)
    {
        /** @var \Magenest\QuickBooksOnline\Model\SalesReceiptAddress $salesAddress */
        $salesAddress = $this->address->create()->load($id);
        $customer = $order->getCustomerName();
        $arrayCustomer = explode(' ', $customer);
        $addressArray = [
            'firstname'    => $arrayCustomer[0], //address Details
            'lastname'     => $arrayCustomer[1],
            'street' => $salesAddress->getStreet(),
            'city' => $salesAddress->getCity(),
            'country_id' => $salesAddress->getCountryId(),
            'postcode' => $salesAddress->getPostcode(),
            'telephone' => '123456789',
            'fax' => '123123',
            'save_in_address_book' => 1
        ];

        return $addressArray;
    }

    /**
     * @param $id
     * @return array
     */
    public function getItem($id)
    {
        /** @var \Magenest\QuickBooksOnline\Model\SalesReceiptProduct $salesProduct */
        $salesProduct = $this->product->create()->getCollection()->addFieldToFilter('qborder_id', $id);
        $result = [];
        foreach ($salesProduct as $product) {
            $result[] = [
                'product_id'=> $product->getProductId(),
                'qty'=> $product->getQty(),
                'price'=> $product->getPrice()
            ];
        }

        return $result;
    }
}
