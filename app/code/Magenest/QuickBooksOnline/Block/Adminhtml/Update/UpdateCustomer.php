<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Update;

use Magento\Backend\Block\Template;

/**
 * Class UpdateCustomer
 * @package Magenest\QuickBooksOnline\Block\Adminhtml
 */
class UpdateCustomer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'update/customer.phtml';

    /**
     * Update constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getInformationCustomer()
    {
        return $this->getUrl('qbonline/update_customer/update');
    }

    public function saveCustomer()
    {
        return $this->getUrl('qbonline/update_customer/save');
    }
}
