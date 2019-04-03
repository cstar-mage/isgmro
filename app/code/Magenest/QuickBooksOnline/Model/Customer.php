<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getCustomerId()
 * @method int getQboId()
 * @method string getFirstname()
 * @method string getLastname()
 * @method string getEmail()
 * @method string getWebsiteId()
 * @method string getGroupId()
 * @method int getDefaultBilling()
 * @method int getDefaultShipping()
 */
class Customer extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\Customer');
    }
}
