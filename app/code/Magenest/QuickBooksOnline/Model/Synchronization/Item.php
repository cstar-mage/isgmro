<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Catalog\Model\ProductFactory;
use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Item
 * @package Magenest\QuickBooksOnline\Model\Sync
 * @method Product getModel()
 */
class Item extends Synchronization
{
    /**
     * @var Category
     */
    protected $_category;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Account
     */
    protected $_account;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $unSupportedChar = ["′", "″", "‴", "⁗", ":"];

    /**
     * Item constructor.
     * @param Client $client
     * @param Log $log
     * @param \Magenest\QuickBooksOnline\Model\Category $category
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductFactory $productFactory
     * @param Account $account
     */
    public function __construct(
        Client $client,
        Log $log,
        \Magenest\QuickBooksOnline\Model\Category $category,
        \Psr\Log\LoggerInterface $logger,
        ProductFactory $productFactory,
        Account $account
    )
    {
        parent::__construct($client, $log);
        $this->_productFactory = $productFactory;
        $this->_account = $account;
        $this->_category = $category;
        $this->type = 'item';
        $this->logger = $logger;
    }

    /**
     * Sync Product to Item
     *
     * @param $id
     * @param bool $update
     * @return mixed
     * @throws LocalizedException
     */
    public function sync($productId, $update = false)
    {
        /** @var \Magento\Catalog\Block\Product $model */
        $model = $this->_productFactory->create()->load($productId);
        $type = (string)$model->getTypeId();

        if ($type == "configurable") {
            $parentId = $productId;
            $arrayId = [];
            $id = $this->sendItems($parentId, $update);
            $usedProducts = $model->getTypeInstance()->getUsedProducts($model);
            foreach ($usedProducts as $child) {
                $childId[] = $child->getId();
                $arrayId = array_merge($childId);
            }
            foreach ($arrayId as $productId) {
                $id = $this->sendItems($productId, $update);
            }
        } else {
            $id = $this->sendItems($productId, $update);
        }

        return $id;
    }

    /**
     * @param $id
     * @param bool $update
     * @return mixed
     * @throws LocalizedException
     */
    public function sendItems($id, $update = false)
    {
        $model = $this->_productFactory->create()->load($id);
        $this->setModel($model);
        $sku = $model->getSku();

        $product = $this->checkProduct($sku);

        if (isset($product['Id'])) { // && !$update) {
            return $product['Id'];
        }
        $this->prepareParams();
        $params = array_replace_recursive($this->getParameter(), $product);
        try {
            $response = $this->sendRequest(\Zend_Http_Client::POST, 'item?minorversion=6', $params);
            $qboId = $response['Item']['Id'];
            $this->addLog($id, $qboId);
            $this->parameter = [];
            return $qboId;
        } catch (LocalizedException $e) {
            $this->addLog($id, null, $e->getMessage());
        }
        $this->parameter = [];
    }

    /**
     * Set Model
     *
     * @param \Magento\Catalog\Model\Product $model
     * @return $this
     */
    public function setModel($model)
    {
        if (empty($model->getId()))
            $model = $this->getDeletedModel($model);
        $name = $model->getSku();
        foreach ($this->unSupportedChar as $char) {
            $name = str_replace($char, " ", $name);
        }
        $name = trim($name);
        $name = substr($name, 0, 100);
        $model->setName($name);

        $sku = $model->getSku();
        $sku = substr($sku, 0, 100);
        $model->setSku($sku);

        $this->_model = $model;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $model
     */
    protected function getDeletedModel($model){
        $model->setName('DeletedItem');
        $model->setSku('DeletedItem');
        $model->setShortDescription('DeletedItem');
        $model->setPrice(0);
        $model->setCreatedAt('1970-01-01 00:00:00');
        return $model;
    }
    /**
     * Prepare params for request
     *
     * @return $this
     */
    protected function prepareParams()
    {
        $account = $this->_account;
        $model = $this->getModel();
//        $catIds = $model->getCategoryIds();
        $name = $model->getName();

        $qty = $model->getExtensionAttributes()->getStockItem()->getQty();
        $params = [
            'Name' => $model->getSku(),
            'Description' => $model->getName(),
            'Active' => true,
            'PurchaseDesc' => $name,
            'UnitPrice' => $model->getPrice(),
            'Taxable' => $model->getTaxClassId() == 0 ? false : true,
            'Sku' => $model->getSku(),
            'FullyQualifiedName' => $name,
            'Type' => 'NonInventory',
            'IncomeAccountRef' => ['value' => $account->sync(), 'name' => $model->getSku()],
            'ExpenseAccountRef' => ['value' => $account->sync('expense')]
        ];

//        if (!empty($catIds)) {
//            $categoryId = $catIds['0'];
//            $catModel = $this->_category->loadByCategoryId($categoryId);
//            if ($catModel->getId()) {
//                $params['SubItem'] = true;
//                $params['ParentRef']['value'] = $catModel->getQboId();
//            }
//        }
        if (trim($model->getTypeId()) !== "configurable") {
            $paramSub = [
                'AssetAccountRef' => ['value' => $account->sync('asset')],
                'QtyOnHand' => empty($qty) ? 0 : $qty,
                'Type' => 'Inventory',
                'InvStartDate' => $model->getCreatedAt(),
                'TrackQtyOnHand' => true,
            ];
            $params = array_replace_recursive($params, $paramSub);
        }
        $this->setParameter($params);

        return $this;
    }

    /**
     * Check item on QBO
     *
     * @param  $name
     * @return array|bool
     */
    public function checkProduct($name)
    {
        $name = trim(addslashes($name));
        $query = "SELECT * FROM Item WHERE Name = '{$name}'";
        //$query = "SELECT Id, SyncToken FROM Item WHERE Name ='{$name}'";
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$result = $this->query($query);
$logger->info($name);
$logger->info($result);
        return $result;
    }

    /**
     * Delete Product
     *
     * @param $name
     */
    public function delete($sku)
    {
        $product = $this->checkProduct($sku);
        if (!empty($product)) {
            $params = [
                'Id' => $product['Id'],
                'SyncToken' => $product['SyncToken'],
                'Active' => false,
            ];

            $this->sendRequest(\Zend_Http_Client::POST, 'item', $params);
        }
    }

    /**
     * Query Product
     *
     * @param  $fistName
     * @param  $lastName
     * @return bool|array
     */
    public function getProduct($params)
    {
        $query = "select * from Item";
        if (isset($params['type']) && $params['type'] == 'time_start') {
            $input = $params['from'];
            $query = "select * from Item where MetaData.LastUpdatedTime >= '$input'";
        }
        if (isset($params['type']) && $params['type'] == 'time_around') {
            $from = $params['from'];
            $to = $params['to'];
            $query = "select * from Item where MetaData.LastUpdatedTime >= '$from' and MetaData.LastUpdatedTime <= '$to'";
        }
        if (isset($params['type']) && $params['type'] == 'name') {
            $input = $params['input'];
            $query = "select * from Item where Name Like '$input'";
        }
        if (isset($params['type']) && $params['type'] == 'id') {
            $input = $params['input'];
            $query = "select * from Item where  Id = '$input'";
        }
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }

    /**
     * count product
     *
     * @return mixed
     */
    public function getCountProduct()
    {
        $query = "select COUNT(*) from Item ";
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result['totalCount'];
    }

    /**
     * list all produc when creat new
     * @return mixed
     */
    public function listProduct($start)
    {
        $query = "select * from Item startposition {$start} maxresults 100";
        $path = 'query?query=' . rawurlencode($query);
        $responses = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $result = $responses['QueryResponse'];

        return $result;
    }


}
