<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksOnline\Setup;

use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package Magenest\QuickBooksOnline\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**@#+
     * @constant
     */
    const TABLE_PREFIX = 'magenest_qbonline_';

    /**
     * Upgrade database when run bin/magento setup:upgrade from command line
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_qbonline_oauth'),
                'website_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Website Id'
                ]
            );
            $this->createQbQueueTable($installer);
            $this->createQboLogTable($installer);
        }

        if (version_compare($context->getVersion(), '2.1.2') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_qbonline_oauth'),
                'refresh_token',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Refresh Token'
                ]
            );
            $this->createCreateCustomerTable($installer);
            $this->createCreateCustomerAddressTable($installer);
            $this->createCreateProductTable($installer);
            $this->createCreateOrderTable($installer);
            $this->createCreateOrderProductTable($installer);
            $this->createCreateOrderPaymentTable($installer);
            $this->createCreateOrderAddressTable($installer);
            $this->createMappingTaxTable($installer);
        }

        if (version_compare($context->getVersion(), '2.2.2') < 0) {
            $this->modifyOauthTable($installer);
        }
        $installer->endSetup();
    }

    private function modifyOauthTable($installer)
    {
        $table = $installer->getTable('magenest_qbonline_oauth');
        $installer->getConnection()->modifyColumn(
            $table,
            'app_username',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'app_tenant',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'oauth_request_token',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'oauth_request_token_secret',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'oauth_access_token',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'oauth_access_token_secret',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'qb_realm',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'qb_flavor',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
        $installer->getConnection()->modifyColumn(
            $table,
            'qb_user',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null
            ]
        );
    }

    /**
     * Create the table magenest_qbonline_create_customer
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateOrderTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_order';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'qborder_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Qborder Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qbo Id'
        )->addColumn(
            'doc_number',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Doc Number'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Status'
        )->addColumn(
            'customer_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Customer Name'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Customer Id'
        )->addColumn(
            'product',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Product'
        )->addColumn(
            'billing_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Billing Id'
        )->addColumn(
            'shipping_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Shipping Id'
        )->addColumn(
            'payment_method',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Payment Method'
        )->addColumn(
            'payment_number',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Payment Number'
        )->addColumn(
            'subtotal',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Subtotal'
        )->addColumn(
            'shipping_amount',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Shipping Amount'
        )->addColumn(
            'tax_amount',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Tax Amount'
        )->addColumn(
            'grand_total',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Grand Total'
        )->addColumn(
            'created_at',
            Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Created At'
        )->addColumn(
            'currency',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Currency'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Email'
        )->setComment(
            'Create Order Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_create_order_payment
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateOrderPaymentTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_order_payment';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'order_payment_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Order Payment Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qbo Id'
        )->addColumn(
            'qborder_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qborder Id'
        )->addColumn(
            'payment_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Payment Name'
        )->setComment(
            'Create Order Payment Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_create_order_payment
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateOrderAddressTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_order_address';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'order_address_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Order Address Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Qbo Id'
        )->addColumn(
            'payment_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Payment Name'
        )->addColumn(
            'street',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Street'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'City'
        )->addColumn(
            'country_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Country Id'
        )->addColumn(
            'region_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Region Id'
        )->addColumn(
            'region',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Region'
        )->addColumn(
            'postcode',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Postcode'
        )->addColumn(
            'company',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Company'
        )->setComment(
            'Create Order Address Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_create_customer
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateOrderProductTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_order_product';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'order_product_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Order Product Id'
        )->addColumn(
            'qborder_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qborder Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qbo Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'name'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'sku'
        )->addColumn(
            'item_status',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Item Status'
        )->addColumn(
            'original_price',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Original Price'
        )->addColumn(
            'price',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Price'
        )->addColumn(
            'qty',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Qty'
        )->addColumn(
            'subtotal',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Subtotal'
        )->addColumn(
            'tax_amount',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Tax Amount'
        )->addColumn(
            'tax_percent',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Tax Percent'
        )->addColumn(
            'discount_amount',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Discount Amount'
        )->addColumn(
            'row_total',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Row Total'
        )->setComment(
            'Create Customer Product Table'
        );

        $installer->getConnection()->createTable($table);
    }


    /**
     * Create the table magenest_qbonline_create_customer
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateCustomerTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_customer';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Customer Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Qbo Id'
        )->addColumn(
            'prefix',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Prefix'
        )->addColumn(
            'firstname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Firstname'
        )->addColumn(
            'middlename',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Middle Name'
        )->addColumn(
            'lastname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Lastname'
        )->addColumn(
            'suffix',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Suffix'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Email'
        )->addColumn(
            'dob',
            Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Date Of Birth'
        )->addColumn(
            'website_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Website Id'
        )->addColumn(
            'group_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Group Id'
        )->addColumn(
            'vat_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Vat Id'
        )->addColumn(
            'gender',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'gender'
        )->addColumn(
            'sendemail_store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Sendemail Store Id'
        )->addColumn(
            'default_billing',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Default Billing'
        )->addColumn(
            'default_shipping',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Default Billing'
        )->setComment(
            'Create Customer Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_create_customer
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateProductTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_product';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Product Id'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false,],
            'Qbo Id'
        )->addColumn(
            'status',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Status'
        )->addColumn(
            'type_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Type Id'
        )->addColumn(
            'attribute_set_id',
            Table::TYPE_INTEGER,
            255,
            ['nullable' => true],
            'Attribute Set Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Name'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Sku'
        )->addColumn(
            'price',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Price'
        )->addColumn(
            'tax_class_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax Class Id'
        )->addColumn(
            'qty',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Qty'
        )->addColumn(
            'is_in_stock',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Is In Stock'
        )->addColumn(
            'visibility',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Visibility'
        )->addColumn(
            'category_ids',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Category Ids'
        )->addColumn(
            'website_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Website Id'
        )->addColumn(
            'store',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'store'
        )->addColumn(
            'child_product',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Child Product'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Description'
        )->setComment(
            'Create Product Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_create_customer_address
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateCustomerAddressTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'create_customer_address';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Entity Id'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Parent Id'
        )->addColumn(
            'prefix',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Prefix'
        )->addColumn(
            'firstname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Firstname'
        )->addColumn(
            'middlename',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Middle Name'
        )->addColumn(
            'lastname',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Lastname'
        )->addColumn(
            'suffix',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Suffix'
        )->addColumn(
            'street',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Street'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'City'
        )->addColumn(
            'country_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Country Id'
        )->addColumn(
            'region_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Region Id'
        )->addColumn(
            'region',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Region'
        )->addColumn(
            'postcode',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Postcode'
        )->addColumn(
            'telephone',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Telephone'
        )->addColumn(
            'fax',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Fax'
        )->addColumn(
            'vat_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Vat Id'
        )->setComment(
            'Create Customer Address Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_queue
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createQbQueueTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'queue';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'queue_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Queue Id'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'operation',
            Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Operation'
        )->addColumn(
            'type_id',
            Table::TYPE_TEXT,
            20,
            ['nullable' => true],
            'Entity Id'
        )->addColumn(
            'website_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Website Id'
        )->addColumn(
            'company_id',
            Table::TYPE_TEXT,
            20,
            ['nullable' => true],
            'Company Id'
        )->addColumn(
            'enqueue_time',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'Enqueue Id'
        )->addColumn(
            'priority',
            Table::TYPE_SMALLINT,
            6,
            ['nullable' => true],
            'Priority'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'msg',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Msg'
        )->setComment(
            'Qb Queue Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table `magenest_qbonline_log
     *
     * @param SetupInterface $installer
     */
    private function createQboLogTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'log';
        if ($installer->tableExists($tableName)) {
            $installer->getConnection()->dropTable($tableName);
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'log_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Log Id'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'type_id',
            Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            'Entity Id'
        )->addColumn(
            'website_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Website Id'
        )->addColumn(
            'company_id',
            Table::TYPE_TEXT,
            20,
            ['nullable' => true],
            'Company Id'
        )->addColumn(
            'dequeue_time',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'Enqueue Id'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'msg',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Msg'
        )->setComment(
            'Qb Log Table'
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table `magenest_qbonline_log
     *
     * @param SetupInterface $installer
     */
    private function createMappingTaxTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'tax';
        if ($installer->tableExists($tableName)) {
            $installer->getConnection()->dropTable($tableName);
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Entity ID'
        )->addColumn(
            'tax_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Tax Name'
        )->addColumn(
            'tax_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Tax Id'
        )->addColumn(
            'tax_code',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Tax Code'
        )->addColumn(
            'rate',
            Table::TYPE_DECIMAL,
            [10, 2],
            ['nullable' => true],
            'Rate'
        )->addColumn(
            'qbo_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'QBO Tax Id'
        )->addColumn(
            'tax_rate_value',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Tax Rate Value'
        )->addColumn(
            'tax_rate_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Tax Rate Name'
        )->setComment('Qb Tax Table');

        $installer->getConnection()->createTable($table);
    }
}
