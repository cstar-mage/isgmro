<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Queue;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractQueue;
use Magenest\QuickBooksOnline\Model\QueueFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Invoice
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Invoice extends AbstractQueue
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Invoice constructor.
     * @param Context $context
     * @param QueueFactory $queueFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        QueueFactory $queueFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $queueFactory);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $collections = $this->collectionFactory->create();

        try {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            foreach ($collections as $invoice) {
                $orderId = $invoice->getOrderId();
                $this->addToQueue($invoice->getIncrementId(), 'invoice');
            }
            $this->messageManager->addSuccessMessage('All Invoice(s) added to the queue');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Have an error when added to the queue');
        }

        $this->_redirect('*/*/index');
    }
}
