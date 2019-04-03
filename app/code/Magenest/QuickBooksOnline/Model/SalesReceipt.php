<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class SalesReceipt
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getQborderId()
 * @method int getQboId()
 * @method string getDocNumber()
 * @method string getStatus()
 * @method int getCustomerId()
 * @method string getProduct()
 * @method int getBillingId()
 * @method int getShippingId()
 * @method int getPaymentMethod()
 * @method int getSubtotal()
 * @method int getShippingAmount()
 * @method int getTaxAmount()
 * @method int getGrandTotal()
 * @method int getCreatedAt()
 * @method string getCurrency()
 * @method string getEmail()
 */
class SalesReceipt extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceipt');
    }
}
