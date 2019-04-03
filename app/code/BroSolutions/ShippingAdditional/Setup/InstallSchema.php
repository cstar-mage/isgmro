<?php
namespace BroSolutions\ShippingAdditional\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quoteTable = 'quote';
        $shipmentTable = 'sales_order';

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'shipping_carrier',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Shipping Carrier'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'account_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Account Number'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($shipmentTable),
                'shipping_carrier',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Shipping Carrier'

                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($shipmentTable),
                'account_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Account Number'

                ]
            );
        $setup->endSetup();
    }
}