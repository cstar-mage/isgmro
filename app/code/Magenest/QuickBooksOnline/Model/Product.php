<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Product
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getProductId()
 * @method int getQboId()
 * @method string getStatus()
 * @method string getTypeId()
 * @method string getAttributeSetId()
 * @method string getName()
 * @method string getSku()
 * @method int getPrice()
 * @method int getTaxClassId()
 * @method string getQty()
 * @method string getIsInStock()
 * @method string getVisibility()
 * @method string getCategoryIds()
 * @method string getUrlKey()
 * @method string getStore()
 * @method string getDescription()
 *
 */
class Product extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\Product');
    }
}
