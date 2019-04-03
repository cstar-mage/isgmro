<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class General
 * @package Magenest\QuickBooksOnline\Block\Adminhtml\Create\Product\View\Tab
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
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currency;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->currency = $currencyFactory;
        $this->_systemStore = $systemStore;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model \Magenest\QuickBooksOnline\Model\Product */
        $model = $this->_coreRegistry->registry('product');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Default')]);

        if ($model->getId()) {
            $fieldset->addField(
                'product_id',
                'hidden',
                [
                    'name' =>'product_id'
                ]
            );
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Enabled Product'),
                'title' => __('Enabled Product'),
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'required' => true,
            ]
        ); $fieldset->addField(
            'attribute_set_id',
            'select',
            [
            'name'     => 'store',
            'label'    => __('Attribute Set'),
            'title'    => __('Attribute Set'),
            'values'   => $this->getAttributeSet(),
            ]
        );
        $fieldset->addField(
            'type_id',
            'select',
            [
                'name' => 'type_id',
                'label' => __('Type Product'),
                'title' => __('Type Product'),
                'values' => $this->getType(),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'store',
            'select',
            [
                'name'     => 'store',
                'label'    => __('Store View'),
                'title'    => __('Store View'),
                'values'   => $this->getStore(),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('Sku'),
                'title' => __('Sku'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'placeholder' => __('$'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class_id',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'options' => ['0' => __('None'), '1' => __('Taxabled Goods')],
            ]
        );
        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Quantity'),
                'title' => __('Quantity'),
            ]
        );
        $fieldset->addField(
            'is_in_stock',
            'select',
            [
                'name' => 'is_in_stock',
                'label' => __('Stock Status'),
                'title' => __('Stock Status'),
                'options' => ['1' => __('In Stock'), '0' => __('Out of Stock')],
            ]
        );
        $fieldset->addField(
            'visibility',
            'select',
            [
                'name' => 'visibility',
                'label' => __('Visibility'),
                'title' => __('Visibility'),
                'options' => [
                    '1' => __('Not Visible Individually'),
                    '2' => __('Catalog'),
                    '3' => __('Search'),
                    '4' => __('Catalog, Search')],
            ]
        );
        $fieldset->addField(
            'category_ids',
            'multiselect',
            [
                'name' => 'category_ids[]',
                'label' => __('Categories'),
                'title' => __('Categories'),
                'values' => $this->getCategory(),
            ]
        )->setSize(5);
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height:10em',
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * get Category
     * @return mixed
     */
    protected function getCategory()
    {
        $data = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magenest\QuickBooksOnline\Model\Config\Option\Category')
            ->toOptionArray();

        return $data;
    }

    /**
     * get all attributes set
     * @return mixed
     */
    protected function getAttributeSet()
    {
        $data = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product\AttributeSet\Options')
            ->toOptionArray();

        return $data;
    }

    /**
     * get all type product
     * @return mixed
     */
    public function getType()
    {
        $data = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product\Type')
        ->toOptionArray();
        unset($data[2]);
        unset($data[4]);
        unset($data[5]);
        
        return $data;
    }

    /**
     * get all store view
     * @return array
     */
    public function getStore()
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
        return __('Product Default');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Default');
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
