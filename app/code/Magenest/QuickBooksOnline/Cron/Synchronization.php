<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksOnline extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_QuickBooksOnline
 * @author   Magenest JSC
 */
namespace Magenest\QuickBooksOnline\Cron;

use Magenest\QuickBooksOnline\Model\ResourceModel\Queue\CollectionFactory;
use Magenest\QuickBooksOnline\Model\Synchronization\Customer;
use Magenest\QuickBooksOnline\Model\Synchronization\Item;
use Magenest\QuickBooksOnline\Model\Synchronization\Order;
use Magenest\QuickBooksOnline\Model\Synchronization\Invoice;
use Magenest\QuickBooksOnline\Model\Synchronization\Creditmemo;
use Magento\Framework\Message\ManagerInterface;
use Magenest\QuickBooksOnline\Model\Config;

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
     * @var Config
     */
    protected $config;

    /**
     * Synchronization constructor.
     * @param CollectionFactory $collectionFactory
     * @param Customer $customer
     * @param Item $item
     * @param Order $order
     * @param Invoice $invoice
     * @param Creditmemo $creditmemo
     * @param ManagerInterface $messageManager
     * @param Config $config
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Customer $customer,
        Item $item,
        Order $order,
        Invoice $invoice,
        Creditmemo $creditmemo,
        ManagerInterface $messageManager,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customer = $customer;
        $this->item = $item;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->messageManager = $messageManager;
        $this->config = $config;
    }

    /**
     *
     */
    public function execute()
    {
        // get config time
        $timeCustomer = $this->config->getCronTimeByType('customer');
        $timeItem = $this->config->getCronTimeByType('item');
        $timeOrder = $this->config->getCronTimeByType('order');
        $timeInvoice = $this->config->getCronTimeByType('invoice');
        $timeCreditmemo = $this->config->getCronTimeByType('creditmemo');

        // get sync mode
        $modeCustomer = $this->config->getSyncModeByType('customer');
        $modeItem = $this->config->getSyncModeByType('item');
        $modeOrder = $this->config->getSyncModeByType('order');
        $modeInvoice = $this->config->getSyncModeByType('invoice');
        $modeCreditmemo = $this->config->getSyncModeByType('creditmemo');

        $minute  = date('i');
        $hour = date('H');
        $time = $hour*60 + $minute;

        //execute
        try {
            // cron sync with customer
            if ($modeCustomer == 2) {
                $this->syncCustomer();
            } elseif ($modeCustomer == 1 && $timeCustomer && ($time % $timeCustomer == 0)) {
                $this->syncCustomer();
            }

            // cron sync with item
            if ($modeItem == 2) {
                $this->syncItem();
            } elseif ($modeItem == 1 && $timeItem && ($time % $timeItem == 0)) {
                $this->syncItem();
            }

            // cron sync with order
            if ($modeOrder == 2) {
                $this->syncOrder();
            } elseif ($modeOrder == 1 && $timeOrder && ($time % $timeOrder == 0)) {
                $this->syncOrder();
            }

            // cron sync with invoice
            if ($modeInvoice == 2) {
                $this->syncInvoice();
            } elseif ($modeInvoice == 1  && $timeInvoice && ($time % $timeInvoice == 0)) {
                $this->syncInvoice();
            }

            // cron sync with creditmemo
            if ($modeCreditmemo == 2) {
                $this->syncCreditmemo();
            } elseif ($modeCreditmemo == 1 && $timeCreditmemo && ($time % $timeCreditmemo == 0)) {
                $this->syncCreditmemo();
            }

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
