<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Update\Product
 */
class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var
     */
    protected $address;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $action;

    /**
     * Save constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action
    ) {
        $this->productModel = $productFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $loggerInterface;
        $this->_layout = $layout;
        $this->action = $action;
        parent::__construct($context);
    }

    /**
     * return json customer
     *
     * @return mixed
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($params['product']) && !empty($params['product'])) {
            $data = $params['product'];
            try {
                foreach ($data as $_data) {
                    $productModel = $this->productModel->create()->getCollection()
                        ->addFieldToFilter('name', $_data['name'])->getFirstItem();
                    if (!empty($productModel->getData())) {
                        // update price
                        if (!empty($_data['price'])) {
                            $productModel->setPrice($_data['price'])->save();
                        }

                        // update qty
                        if (!empty($_data['qty'])) {
                            $stockItem = $this->_objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface')
                                ->getStockItem($productModel->getId());
                            $stockItem->setData('qty', $_data['qty'])->save();
                        }

                        // update description
                        if (!empty($_data['description'])) {
                            $this->action->updateAttributes([$productModel->getId()], ['short_description' => $_data['description']], $productModel->getStoreId());
                        }
                        $this->messageManager->addSuccessMessage(__('This product(%1) have updated.', $_data['name']));
                    } else {
                        $this->messageManager->addErrorMessage(__('This product(%1) don\'t exist.', $_data['name']));
                    }
                }
                $this->messageManager->addSuccessMessage('All Product(s) have updated.');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the product.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                return  $resultRedirect->setPath('qbonline/update_customer/index', ['_current' => true]);
            }
        }

        return  $resultRedirect->setPath('qbonline/update_product/index', ['_current' => true]);
    }
}
