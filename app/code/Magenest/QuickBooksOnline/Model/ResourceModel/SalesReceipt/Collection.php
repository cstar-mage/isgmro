<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceipt;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceipt
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'qborder_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\SalesReceipt', 'Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceipt');
    }
}
