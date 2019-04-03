<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateSalesReceipt;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\QuickBooksOnline\Model\SalesReceiptFactory;
use Magenest\QuickBooksOnline\Model\SalesReceiptProductFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Salesreceipt
 */
class MassDelete extends AbstractCreateSalesReceipt
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var SalesReceiptProductFactory
     */
    protected $product;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param SalesReceiptFactory $salesReceiptFactory
     * @param SalesReceiptProductFactory $salesReceiptProductFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        SalesReceiptFactory $salesReceiptFactory,
        SalesReceiptProductFactory $salesReceiptProductFactory
    ) {
        parent::__construct($context, $salesReceiptFactory);
        $this->filter = $filter;
        $this->product = $salesReceiptProductFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $salesReceipt = $this->salesReceiptFactory->create()->getCollection();
        $salesProduct = $this->product->create()->getCollection();

        $collection = $this->filter->getCollection($salesReceipt);
        $i = 0;
        /** @var \Magenest\QuickBooksOnline\Model\Product $product */
        foreach ($collection->getItems() as $sale) {
            $salesProduct->addFieldToFilter('qborder_id', $sale->getId());
            $j = 0;
            foreach ($salesProduct as $product) {
                $product->delete();
                $j++;
            }
            $sale->delete();
            $i++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $i)
        );

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        
        return $resultRedirect;
    }
}
