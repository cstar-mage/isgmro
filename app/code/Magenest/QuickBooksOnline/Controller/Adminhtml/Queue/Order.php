<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Queue;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractQueue;
use Magenest\QuickBooksOnline\Model\QueueFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Order
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Order extends AbstractQueue
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Customer constructor.
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
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collections as $order) {
                $this->addToQueue($order->getIncrementId(), 'order');
            }
            $this->messageManager->addSuccessMessage('All Order(s) added to the queue');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Have an error when added to the queue');
        }

        $this->_redirect('*/*/index');
    }
}
