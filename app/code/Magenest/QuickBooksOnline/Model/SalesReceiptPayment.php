<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class SalesReceiptPayment
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getOrderPaymentId()
 * @method int getQboId()
 * @method string getPaymentName()
 * @method int getQborderId()
 */
class SalesReceiptPayment extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceiptPayment');
    }
}
