<?php

namespace BroSolutions\ImportAttributes\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    /**
    * EAV setup factory
    *
    * @var EavSetupFactory
    */
    private $eavSetupFactory;

    /**
    * Init
    *
    * @param EavSetupFactory $eavSetupFactory
    * @param AttributeSetFactory $attributeSetFactory
    */
    public function __construct(EavSetupFactory $eavSetupFactory, AttributeSetFactory $attributeSetFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
    * {@inheritdoc}
    */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /*dropdown attributes start*/

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'coating', [
                'type' => 'int',
                'label' => 'Coating',
                'input' => 'select',
                //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => 0,
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 51,
                'group' => 'BroSolutions Custom',
                'option'         => [
                    'values' => [
                    ]
                ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'color', [
            'type' => 'int',
            'label' => 'Color',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 52,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [
                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'finish', [
            'type' => 'int',
            'label' => 'Finish',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 53,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'gauge', [
            'type' => 'int',
            'label' => 'Gauge',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 54,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'grade', [
            'type' => 'int',
            'label' => 'Grade',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 55,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'height', [
            'type' => 'int',
            'label' => 'Height',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 56,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'length', [
            'type' => 'int',
            'label' => 'Length',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 57,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'material', [
            'type' => 'int',
            'label' => 'Material',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 58,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'polarity', [
            'type' => 'int',
            'label' => 'Polarity',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 59,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'pressure', [
            'type' => 'int',
            'label' => 'Pressure',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 60,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'rating', [
            'type' => 'int',
            'label' => 'Rating',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 61,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'size', [
            'type' => 'int',
            'label' => 'Size',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 62,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'system_of_measurement', [
            'type' => 'int',
            'label' => 'System of Measurement',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 63,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'thickness', [
            'type' => 'int',
            'label' => 'Thickness',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 64,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'thread_type', [
            'type' => 'int',
            'label' => 'Thread Type',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 65,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'type', [
            'type' => 'int',
            'label' => 'Type',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 66,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);
//weight is system attribute
//        $eavSetup->addAttribute(
//            \Magento\Catalog\Model\Product::ENTITY,
//            'weight', [
//            'type' => 'static',
//            'label' => 'Weight',
//            'input' => 'select',
//            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
//            'required' => false,
//            'user_defined' => true,
//            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
//            'visible' => true,
//            'default' => 0,
//            'searchable' => true,
//            'filterable' => true,
//            'comparable' => true,
//            'visible_on_front' => true,
//            'used_in_product_listing' => true,
//            'unique' => false,
//            'apply_to' => '',
//            'sort_order' => 67,
//            'group' => 'BroSolutions Custom',
//            'option'         => [
//                'values' => [
//
//                ]
//            ],
//        ]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'width', [
            'type' => 'int',
            'label' => 'Width',
            'input' => 'select',
            //'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'default' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
            'sort_order' => 68,
            'group' => 'BroSolutions Custom',
            'option'         => [
                'values' => [

                ]
            ],
        ]);

        /*dropdown attributes end*/


        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'tag', [
                'type' => 'text', //was varchar
                'input' => 'text',
                'label' => 'Tag',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 101,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'opencart_product_id', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Product ID',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 102,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'model', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Model',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 103,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'c2c_upc', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'UPC',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 104,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'ean', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'EAN',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 105,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'jan', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'JAN',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 106,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'isbn', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'ISBN',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 107,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mpn', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'MPN',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 108,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'c2c_location', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Location',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 109,
                'group' => 'BroSolutions Custom',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'opencart_views', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Viewed',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 110,
                'group' => 'BroSolutions Custom',
            ]
        );




//
//        $eavSetup->addAttribute(
//            \Magento\Catalog\Model\Product::ENTITY,
//            'custom_size_attr', [
//                'group' => 'BroSolutions Custom',
//                'type' => 'int',
//                'backend' => '',
//                'frontend' => '',
//                'label' => 'Custom Size Attribute',
//                'input' => 'swatch_text',
//                'class' => '',
//                'source' => '',
//                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
//                'visible' => true,
//                'required' => false,
//                'user_defined' => true,
//                'default' => 0,
//                'searchable' => true,
//                'filterable' => true,
//                'comparable' => true,
//                'visible_on_front' => true,
//                'used_in_product_listing' => true,
//                'unique' => false,
//                'apply_to' => '',
//                'sort_order' => 101,
//                'option' => [ // temporary
//                    'values' => [
//                    ],
//                ],
//            ]
//        );

        $setup->endSetup();
    }
}