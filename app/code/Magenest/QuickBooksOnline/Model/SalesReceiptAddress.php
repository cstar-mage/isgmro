<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class SalesReceiptAddress
 *
 * @package Magenest\QuickBooksOnline\Model
 *
 * @method int getOrderAddressId()
 * @method int getQboId()
 * @method string getPaymentName()
 * @method string getStreet()
 * @method string getCity()
 * @method string getCountryId()
 * @method string getRegionId()
 * @method string getRegion()
 * @method string getPostcode()
 * @method string getCompany()
 * @method string getTelephone()
 * method string getFax()
 */
class SalesReceiptAddress extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksOnline\Model\ResourceModel\SalesReceiptAddress');
    }
}
