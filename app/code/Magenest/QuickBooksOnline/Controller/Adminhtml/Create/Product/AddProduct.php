<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product;

use Magenest\QuickBooksOnline\Controller\Adminhtml\AbstractCreateProduct;
use Magenest\QuickBooksOnline\Model\ProductFactory;
use Magenest\QuickBooksOnline\Model\Synchronization\Item as SyncItem;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory as DefaultProduct;

/**
 * Class AddProduct
 * @package Magenest\QuickBooksOnline\Controller\Adminhtml\Create\Product
 */
class AddProduct extends AbstractCreateProduct
{
    /**
     * @var SyncItem
     */
    protected $syncItem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var DefaultProduct
     */
    protected $product;

    /**
     * @var \Magenest\QuickBooksOnline\Model\Config
     */
    protected $config;

    /**
     * AddProduct constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param SyncItem $syncItem
     * @param \Psr\Log\LoggerInterface $logger
     * @param DefaultProduct $defaultProduct
     * @param \Magenest\QuickBooksOnline\Model\Config $config
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        SyncItem $syncItem,
        \Psr\Log\LoggerInterface $logger,
        DefaultProduct $defaultProduct,
        \Magenest\QuickBooksOnline\Model\Config $config
    ) {
        parent::__construct($context, $productFactory);
        $this->syncItem = $syncItem;
        $this->logger = $logger;
        $this->product = $defaultProduct;
        $this->config = $config;
    }

    /**
     * Execute the action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $connect = $this->config->getConnected();
        if ($connect && $connect == 1) {
            $modelProduct = $this->productFactory->create()->getCollection();
            foreach ($modelProduct as $products) {
                $products->delete();
            }
            $arrayStart = $this->countProduct();
            foreach ($arrayStart as $start) {
                $collections = $this->syncItem->listProduct($start);
                if (isset($collections['Item'])) {
                    try {
                        /** @var \Magento\Customer\Model\Customer $customer */
                        foreach ($collections['Item'] as $information) {
                            if ($this->checkProduct($information['Name'])) {
                                $this->addToProductTb($information);
                            }
                        }
                        $this->messageManager->addSuccessMessage('All products added');
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage('Have an error when added this');
                    }
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Not connect to QuickBooks Online'));
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @return array
     */
    public function countProduct()
    {
        $count = $this->syncItem->getCountProduct();
        $array = [1];
        if ($count > 100) {
            $check = round($count/100, 0);
            for ($i =1; $i <= $check; $i ++) {
                $value = $i *100;
                array_push($array, $value);
            }
        }

        return $array;
    }

    /**
     * Add to Queue
     *
     * @param $id
     * @param $type
     */
    public function addToProductTb($information)
    {
        $model = $this->productFactory->create();

        $typeQbo = $information['Type'];
        $typeId = '';

        if ($typeQbo == 'Inventory' || $typeQbo == 'Non Inventory') {
            $typeId = 'simple';
        }
        if ($typeQbo == 'Service') {
            $typeId = 'virtual';
        }
        if ($typeQbo == 'Bunble') {
            $typeId = 'bundle';
        }

        $data = [
            'qbo_id' => isset($information['Id']) ? $information['Id'] : null,
            'status' => 1,
            'type_id' => $typeId,
            'attribute_set_id' => 4,
            'name' => isset($information['Name']) ? $information['Name'] : null,
            'sku' => isset($information['Name']) ? strtolower($information['Name']) : null,
            'price' => isset($information['UnitPrice']) ? $information['UnitPrice'] : null,
            'tax_class_id' => 0,
            'qty' => isset($information['QtyOnHand']) ? $information['QtyOnHand'] : null,
            'is_in_stock' => 1,
            'visibility' => 4,
            'store' => 1,
            'category_ids' => 2,
            'description' => isset($information['Description']) ? $information['Description'] : null,
        ];

        $model->addData($data);
        $model->save();
    }

    /**
     * @param $email
     * @return int
     */
    public function checkProduct($name)
    {
        $model = $this->product->create()->getCollection()
            ->addFieldToFilter('name', $name)->getFirstItem();
        $check = 1;
        if (!empty($model->getData())> 0) {
            $check = 0;
        }

        return $check;
    }
}
