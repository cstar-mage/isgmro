<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Helper;

use Magenest\QuickBooksOnline\Model\ResourceModel\Queue\CollectionFactory;
use Magenest\QuickBooksOnline\Model\Synchronization\Customer;
use Magenest\QuickBooksOnline\Model\Synchronization\Item;
use Magenest\QuickBooksOnline\Model\Synchronization\Order;
use Magenest\QuickBooksOnline\Model\Synchronization\Invoice;
use Magenest\QuickBooksOnline\Model\Synchronization\Creditmemo;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Synchronization
 *
 * @package Magenest\QuickBooksOnline\Cron
 */
class Synchronization
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Item
     */
    protected $item;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Creditmemo
     */
    protected $creditmemo;

    /**
     * Synchronization constructor.
     * @param CollectionFactory $collectionFactory
     * @param Customer $customer
     * @param Item $item
     * @param Order $order
     * @param Invoice $invoice
     * @param Creditmemo $creditmemo
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Customer $customer,
        Item $item,
        Order $order,
        Invoice $invoice,
        Creditmemo $creditmemo,
        ManagerInterface $messageManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customer = $customer;
        $this->item = $item;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->messageManager = $messageManager;
    }

    /**
     *
     */
    public function execute()
    {
        //execute
        try {
            $this->syncCustomer();
            $this->syncItem();
            $this->syncOrder();
            $this->syncInvoice();
            $this->syncCreditmemo();
            $this->messageManager->addSuccessMessage('All queues synced to QuickBooks Online');
        } catch (\Exception $e) {
            $details = '';
            $message = $e->getMessage();
            try {
                $parser = new \Magento\Framework\Xml\Parser();
                $parser->loadXML($e->getMessage());
                if ($err = $parser->getDom()->getElementsByTagName('Message')->item(0)) {
                    $message = $err->textContent;
                    $details .= $message;
                }
            } catch (\Exception $e) {
                $details = $message;
            }
            $this->messageManager->addErrorMessage('Error Syncing Data to QuickbookOnlines. Details: ' . $details);
        }
    }

    /**
     * @return mixed
     */
    public function getQueueCollection()
    {
        return $this->collectionFactory->create();
    }

    public function syncCustomer()
    {
        $collection = $this->getQueueCollection();
        $collection->addFieldToFilter('type', 'customer');
        if ($collection->count() > 0) {
            /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->customer->sync($queue->getTypeId(), true);
                $queue->delete();
            }
        }
    }

    public function syncItem()
    {
        $collection = $this->getQueueCollection();
        $collection->addFieldToFilter('type', 'item');
        if ($collection->count() > 0) {
            /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->item->sync($queue->getTypeId(), true);
                $queue->delete();
            }
        }
    }

    public function syncOrder()
    {
        $collection = $this->getQueueCollection();
        $collection->addFieldToFilter('type', 'order');
        if ($collection->count() > 0) {
            /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->order->sync($queue->getTypeId());
                $queue->delete();
            }
        }
    }

    public function syncInvoice()
    {
        $collection = $this->getQueueCollection();
        $collection->addFieldToFilter('type', 'invoice');
        if ($collection->count() > 0) {
            /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->invoice->sync($queue->getTypeId());
                $queue->delete();
            }
        }
    }

    /**
     * sync credit memo
     */
    public function syncCreditmemo()
    {
        $collection = $this->getQueueCollection();
        $collection->addFieldToFilter('type', 'creditmemo');
        if ($collection->count() > 0) {
            /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->creditmemo->sync($queue->getTypeId());
                $queue->delete();
            }
        }
    }
}
