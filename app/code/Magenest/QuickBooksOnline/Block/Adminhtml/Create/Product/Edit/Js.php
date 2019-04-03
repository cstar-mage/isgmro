<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\Edit;

use Magento\Backend\Block\Template\Context;

/**
 * Class Js
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\Edit
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magenest\QuickBooksOnline\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * Js constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magenest\QuickBooksOnline\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\QuickBooksOnline\Model\ProductFactory $productFactory,
        array $data
    ) {
        $this->product = $productFactory;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function useAddress()
    {
        $model = $this->product->create()->load($this->_request->getParam('id'));
        $useBilling = 0;
        $useShipping = 0;
        if ($model->getDefaultBilling() > 0) {
            $useBilling = 1;
        }
        if ($model->getDefaultShipping() > 0) {
            $useShipping = 1;
        }

        return [
            'billing' => $useBilling,
            'shipping' => $useShipping,
        ];
    }
}
