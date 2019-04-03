<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Queue;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractQueue;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\QuickBooksOnline\Model\QueueFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Queue
 */
class MassDelete extends AbstractQueue
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        QueueFactory $queueFactory
    ) {
        parent::__construct($context, $queueFactory);
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $a = $this->_request->getParams();
        $queueCollection = $this->queueFactory->create()->getCollection();
        $collection = $this->filter->getCollection($queueCollection);
        $queueDeleted = 0;
        /** @var \Magenest\QuickBooksOnline\Model\Queue $queue */
        foreach ($collection->getItems() as $queue) {
            $queue->delete();
            $queueDeleted++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $queueDeleted)
        );

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
