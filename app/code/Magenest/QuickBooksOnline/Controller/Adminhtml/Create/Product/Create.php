<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product;

use Magenest\QuickBooksOnline\Model\ProductFactory;
use Magento\Backend\App\Action\Context;
use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateProduct;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Create
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product
 */
class Create extends AbstractCreateProduct
{
    /**
     * Create constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory
    ) {
        parent::__construct($context, $productFactory);
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
        $model = $this->productFactory->create()->getCollection();
        /** @var \Magenest\QuickBooksOnline\Model\Product $data */
        foreach ($model as $data) {
            try {
                $product = $this->_objectManager->create('\Magento\Catalog\Model\Product');
                $product->setWebsiteIds([$data->getStore()]);
                $product->setAttributeSetId($data->getAttributeSetId());
                $product->setTypeId($data->getTypeId());
                $product->setCreatedAt(strtotime('now'));
                $product->setName($data->getName());
                $product->setSku($data->getSku());
                $product->setStatus($data->getStatus());
                $category_id= explode(',', $data->getCategoryIds());
                $product->setCategoryIds($category_id);
                $product->setTaxClassId($data->getTaxClassId());
                $product->setVisibility($data->getVisibility());
                $product->setPrice($data->getPrice());
                $product->setMsrpDisplayActualPriceType(1);
                $product->setMetaTitle($data->getName());
                $product->setMetaKeyword($data->getName());
                $product->setMetaDescription($data->getName());
                $product->setDescription($data->getDescription());
                $product->setStockData(
                    [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1, // manage stock
                        'is_in_stock' => $data->getIsInStock(), // Stock Availability of product
                        'qty' => $data->getQty() // qty of product
                    ]
                );
                $product->save();
                $data->delete();
                $this->messageManager->addSuccessMessage('All products have created');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Have an error when created product %1', $data->getName());
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
