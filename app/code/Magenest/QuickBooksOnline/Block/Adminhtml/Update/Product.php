<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Update;

use Magento\Backend\Block\Template;

/**
 * Class Product
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Update
 */
class Product extends Template
{
    /**
     * Default Template
     *
     * @var string
     */
    protected $_template = "Magenest_QuickBooksOnline::update/informationProduct.phtml";

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getPost()
    {
        $data = $this->getPost();

        return $data;
    }
}
