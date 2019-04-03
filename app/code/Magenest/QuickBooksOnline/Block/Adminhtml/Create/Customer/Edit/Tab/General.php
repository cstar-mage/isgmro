<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class General
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Customer\View\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var
     */
    protected $_prepareForm;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var
     */
    protected $_fieldFactory;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Magenest\QuickBooksOnline\Model\Config\Option\Website $website,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->website = $website;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('customer');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);

        if ($model->getId()) {
            $fieldset->addField(
                'customer_id',
                'hidden',
                [
                    'name' =>'customer[customer_id]'
                ]
            );
        }
        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'customer[website_id]',
                'label' => __('Associate to Website'),
                'title' => __('Associate to Website'),
                'options' => ['1' => __('Main Website')],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'group_id',
            'select',
            [
                'name' => 'customer[group_id]',
                'label' => __('Group'),
                'title' => __('Group'),
                'values' => $this->getGroup(),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'prefix',
            'text',
            [
                'name' => 'customer[prefix]',
                'label' => __('Prefix'),
                'title' => __('Prefix'),
            ]
        );
        $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'customer[firstname]',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'middlename',
            'text',
            [
                'name' => 'customer[middlename]',
                'label' => __('Middle Name/Initial'),
                'title' => __('Middle Name/Initial'),
            ]
        );
        $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'customer[lastname]',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'suffix',
            'text',
            [
                'name' => 'customer[suffix]',
                'label' => __('Suffix'),
                'title' => __('Suffix'),
            ]
        );
        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'customer[email]',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,
                'placeholder' => __('@email: ')
            ]
        );
        $fieldset->addField(
            'dob',
            'date',
            [
                'name' => 'customer[dob]',
                'label' => __('Date of Birth'),
                'title' => __('Date of Birth'),
                'date_format' => ('MM/dd/yyyy'),
            ]
        );
        $fieldset->addField(
            'taxvat',
            'text',
            [
                'name' => 'customer[taxvat]',
                'label' => __('Tax/VAT Number'),
                'title' => __('Tax/VAT Number'),
            ]
        );
        $fieldset->addField(
            'gender',
            'select',
            [
                'name' => 'customer[gender]',
                'label' => __('Gender'),
                'title' => __('Gender'),
                'options' => [
                    '3' => __('Not Specified'),
                    '1' => __('Male'),
                    '2' => __('Female')
                ],
            ]
        );

        $fieldset->addField(
            'sendemail_store_id',
            'select',
            [
                'name'     => 'customer[sendemail_store_id]',
                'label'    => __('Send Welcome Email From'),
                'title'    => __('Send Welcome Email From'),
                'values'   => $this->getStoreEmail(),
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $groupOptions = $objectManager->get('\Magento\Customer\Model\ResourceModel\Group\Collection')->toOptionArray();
        unset($groupOptions[0]);
        return $groupOptions;
    }

    /**
     * @return array
     */
    public function getStoreEmail()
    {
        $array = $this->_systemStore->getStoreValuesForForm(false, true);
        unset($array[0]);
        return $array;
    }
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Account Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Account Information');
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
