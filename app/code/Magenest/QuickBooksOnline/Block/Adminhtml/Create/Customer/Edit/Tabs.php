<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit;

/**
 * Class Tabs
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\View
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_base_fieldset');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Customer Information'));
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label'   => __('Account Information'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab\General',
                    'qbonline.create.customer.tab.general'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'billing_address',
            [
                'label'   => __('Billing Addresses'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab\BillingAddress',
                    'qbonline.create.customer.tab.billing.address'
                )->toHtml(),
                'active' => false
            ]
        );
        $this->addTab(
            'shipping_address',
            [
                'label'   => __('Shipping Addresses'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab\ShippingAddress',
                    'qbonline.create.customer.tab.shipping.address'
                )->toHtml(),
                'active' => false
            ]
        );
    }
}
