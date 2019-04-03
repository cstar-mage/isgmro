<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class BillingAddress
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab
 */
class BillingAddress extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * BillingAddress constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\Config\Source\Country $country,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->country = $country;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model \Magenest\QuickBooksOnline\Model\CustomerAddress */
        $model = $this->_coreRegistry->registry('billing');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('billing_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Billing Address')]);

        $fieldset->addField(
            'enabled',
            'select',
            [
                'name' => 'billing[enabled]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'options' => ['0' => __('No'), '1' => __('Yes')],
            ]
        );
        if (!empty($model)) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                [
                    'name' =>'billing[entity_id]'
                ]
            );
        }
        $fieldset->addField(
            'prefix',
            'text',
            [
                'name' => 'billing[prefix]',
                'label' => __('Prefix'),
                'title' => __('Prefix'),
            ]
        );
        $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'billing[firstname]',
                'label' => __('First Name'),
                'title' => __('First Name'),
            ]
        );
        $fieldset->addField(
            'middlename',
            'text',
            [
                'name' => 'billing[middlename]',
                'label' => __('Middle Name/Initial'),
                'title' => __('Middle Name/Initial'),
            ]
        );
        $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'billing[lastname]',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
            ]
        );
        $fieldset->addField(
            'suffix',
            'text',
            [
                'name' => 'billing[suffix]',
                'label' => __('Suffix'),
                'title' => __('Suffix'),
            ]
        );
        $fieldset->addField(
            'street',
            'text',
            [
                'name' => 'billing[street]',
                'label' => __('Street'),
                'title' => __('Street'),
            ]
        );
        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'billing[city]',
                'label' => __('City'),
                'title' => __('City'),
            ]
        );
        $fieldset->addField(
            'country_id',
            'select',
            [
                'name' => 'billing[country_id]',
                'label' => __('Country'),
                'title' => __('Country'),
                'values' => $this->getCountry(),
            ]
        );
        $fieldset->addField(
            'region_id',
            'text',
            [
                'name' => 'billing[region_id]',
                'label' => __('State/Province'),
                'title' => __('State/Province'),
            ]
        );
        $fieldset->addField(
            'postcode',
            'text',
            [
                'name' => 'billing[postcode]',
                'label' => __('Zip/Postal Code'),
                'title' => __('Zip/Postal Code'),
            ]
        );
        $fieldset->addField(
            'telephone',
            'text',
            [
                'name' => 'billing[telephone]',
                'label' => __('Telephone'),
                'title' => __('Telephone'),
            ]
        );
        $fieldset->addField(
            'vat_id',
            'text',
            [
                'name' => 'billing[vat_id]',
                'label' => __('VAT number'),
                'title' => __('VAT number'),
            ]
        );
        if (!empty($model)) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    public function getCountry()
    {
        $country = $this->country->toOptionArray();
        unset($country[0]);
        return $country;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Billing Address');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Billing Address');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
