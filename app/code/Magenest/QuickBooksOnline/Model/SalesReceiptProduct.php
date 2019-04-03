<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class SalesReceiptProduct
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getOrderProductId()
 * @method int getQboId()
 * @method int getQborderId()
 * @method string getName()
 * @method string getSku()
 * @method string getItemStatus()
 * @method string getOriginalPrice()
 * @method int getPrice()
 * @method int getQty()
 * @method int getSubtotal()
 * @method int getTaxAmount()
 * @method int getTaxPercent()
 * @method int getDiscountAmount()
 * @method int getRowTotal()
 */
class SalesReceiptProduct extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceiptProduct');
    }
}
