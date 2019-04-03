<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\Edit;

/**
 * Class Tabs
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\View
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
        $this->setTitle(__('Product Information'));
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
                'label'   => __('Product Default'),
                'content' => $this->getLayout()->createBlock(
                    'Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\Edit\Tab\General',
                    'qbonline.create.product.tab.general'
                )->toHtml(),
                'active' => true
            ]
        );
    }
}
