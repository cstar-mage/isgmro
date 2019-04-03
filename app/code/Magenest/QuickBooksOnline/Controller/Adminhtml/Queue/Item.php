<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Queue;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractQueue;
use Magenest\QuickBooksOnline\Model\QueueFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Customer
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class Item extends AbstractQueue
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
    )
    {
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
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($collections as $product) {
                $this->addToQueue($product->getId(), 'item');
            }
            $this->messageManager->addSuccessMessage('All product(s) added to the queue');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Have an error when added to the queue');
        }

        $this->_redirect('*/*/index');
    }
}
