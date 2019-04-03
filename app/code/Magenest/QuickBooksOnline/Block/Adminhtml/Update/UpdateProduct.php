<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Update;

use Magento\Backend\Block\Template;

/**
 * Class UpdateProduct
 * @package Magenest\QuickBooksOnline\Block\Adminhtml
 */
class UpdateProduct extends Template
{
    /**
     * @var string
     */
    protected $_template = 'update/product.phtml';

    /**
     * Update constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getInformationProduct()
    {
        return $this->getUrl('qbonline/update_product/update');
    }

    public function saveProduct()
    {
        return $this->getUrl('qbonline/update_product/save');
    }
}
