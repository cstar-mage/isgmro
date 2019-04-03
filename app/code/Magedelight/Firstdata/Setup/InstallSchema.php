<?php

namespace Magedelight\Firstdata\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('md_firstdata')
        )->addColumn(
            'card_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Card Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            ['unsigned' => true, 'nullable' => true],
            'Customer ID'
        )->addColumn(
            'firstdata_transarmor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '30',
            [],
            'Customer transarmor Id'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            150,
            [],
            'Card Customer First Name'
        )
        ->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            150,
            [],
            'Card Customer Last Name'
        )
        ->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'PostCode'
        )
        ->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Country ID'
        )
        ->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '150',
            [],
            'Region ID'
        )
        ->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '150',
            [],
            'State'
        )
        ->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '150',
            [],
            'CustomerCity'
        )
        ->addColumn(
            'company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '150',
            [],
            'Customer Company'
        )
        ->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            [],
            'Customer Street'
        )
        ->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '50',
            [],
            'Customer Telephone'
        )
        ->addColumn(
            'cc_exp_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12',
            [],
            'Card Exp Month'
        )
        ->addColumn(
            'cc_last_4',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            [],
            'Card Last Four Digit'
        )
        ->addColumn(
            'cc_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '32',
            [],
            'Card Type'
        )
        ->addColumn(
            'cc_exp_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '4',
            [],
            'Card Exp Year'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Card Creation Time'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Card Modification Time'
        )
        ->addForeignKey(// pri col name         //ref tabname     //ref cou name
            'FIRSTDATA_CUSTOMER_ID',
            'customer_id', // table column name
            $installer->getTable('customer_entity'),   // ref table name
            'entity_id',   // ref column name
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL  // on delete
        )
        ;
        $installer->getConnection()->createTable($table);

        $connection = $installer->getConnection();

        $quotePayment = $installer->getTable('quote_payment');
        $quoteColumns = [];
        $quoteColumns1 = [
            'md_firstdata_transarmor_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Transarmor Id',
            ],
        ];

        foreach ($quoteColumns1 as $name => $definition) {
            $connection->addColumn($quotePayment, $name, $definition);
        }

        $quoteColumns = [
            'md_firstdata_requestid' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Request ID',
            ],
        ];

        foreach ($quoteColumns as $name => $definition) {
            $connection->addColumn($quotePayment, $name, $definition);
        }

        $orderPayment = $installer->getTable('sales_order_payment');

        $orderColumns1 = [
            'md_firstdata_transarmor_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Transarmor Id',
            ],
        ];

        foreach ($orderColumns1 as $name => $definition) {
            $connection->addColumn($orderPayment, $name, $definition);
        }

        $orderColumns = [
            'md_transarmor_requestid' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '30',
                'nullable' => false,
                'comment' => 'Transarmor Id',
            ],
        ];

        foreach ($orderColumns as $name => $definition) {
            $connection->addColumn($orderPayment, $name, $definition);
        }

        $trasactionTag = $installer->getConnection()->tableColumnExists($orderPayment, 'transaction_tag', '');

        if ($trasactionTag == false) {
            $orderpaymentColumns = [
                'transaction_tag' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Transaction Tag',
                ],
            ];

            foreach ($orderpaymentColumns as $name => $definition) {
                $connection->addColumn($orderPayment, $name, $definition);
            }
        }

        $quoteToken = $installer->getConnection()->tableColumnExists($quotePayment, 'firstdata_token', '');

        if ($quoteToken == false) {
            $quoteColumns = [];
            $quoteColumns = [
                'firstdata_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Firstdata Token',
                ],
            ];

            foreach ($quoteColumns as $name => $definition) {
                $connection->addColumn($quotePayment, $name, $definition);
            }
        }

        $orderToken = $installer->getConnection()->tableColumnExists($orderPayment, 'firstdata_token', '');

        if ($orderToken == false) {
            $orderColumns = [];

            $orderColumns = [
                'firstdata_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Firstdata Token',
                ],
            ];

            foreach ($orderColumns as $name => $definition) {
                $connection->addColumn($orderPayment, $name, $definition);
            }
        }

        $invoiceToken = $installer->getConnection()->tableColumnExists($installer->getTable('sales_invoice'), 'firstdata_token', '');

        if ($invoiceToken == false) {
            $invoiceColumns = [];

            $invoiceColumns = [
                'firstdata_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Firstdata Token',
                ],
            ];

            foreach ($invoiceColumns as $name => $definition) {
                $connection->addColumn($installer->getTable('sales_invoice'), $name, $definition);
            }
        }

        $creditToken = $installer->getConnection()->tableColumnExists($installer->getTable('sales_creditmemo'), 'firstdata_token', '');

        if ($creditToken == false) {
            $creditColumns = [];

            $creditColumns = [
                'firstdata_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'comment' => 'Firstdata Token',
                ],
            ];

            foreach ($creditColumns as $name => $definition) {
                $connection->addColumn($installer->getTable('sales_creditmemo'), $name, $definition);
            }
        }

        $installer->endSetup();
    }
}
