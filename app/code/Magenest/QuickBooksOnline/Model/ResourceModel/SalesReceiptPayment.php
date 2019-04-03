<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SalesReceiptPayment
 * @package Magenest\QuickBooksOnline\Model\ResourceModel
 */
class SalesReceiptPayment extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_qbonline_create_order_payment', 'order_payment_id');
    }
}
