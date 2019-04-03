<?php

namespace BroSolutions\Isgmro\Setup;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $version = $context->getVersion();
        if(version_compare($version, '0.1.2', '<')) {
            $tableName = $setup->getTable('sales_order');
            $setup->run("ALTER TABLE ".$tableName." ADD comment_code varchar(1024) NULL;");
        }
        $setup->endSetup();
    }
}
