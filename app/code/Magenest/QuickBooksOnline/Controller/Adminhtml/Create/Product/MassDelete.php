<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateProduct;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\QuickBooksOnline\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product
 */
class MassDelete extends AbstractCreateProduct
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProductFactory $productFactory
    ) {
        parent::__construct($context, $productFactory);
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $customerCollection = $this->productFactory->create()->getCollection();
        $collection = $this->filter->getCollection($customerCollection);
        $i = 0;
        /** @var \Magenest\QuickBooksOnline\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            $product->delete();
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
