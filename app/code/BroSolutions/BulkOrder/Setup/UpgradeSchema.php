<?php

namespace BroSolutions\BulkOrder\Setup;
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
        if(version_compare($version, '0.1.3', '<')) {
            $tableName = $setup->getTable('cms_block');
            //$bulkOrderUrl = $setup->get
            $setup->run("UPDATE ".$tableName." SET `content` = '<h6>my account</h6>
<ul>
<li><a href=\"customer/account/login\">login</a></li>
<li><a href=\"#\">order history</a></li>
<li><a href=\"#\">my lists</a></li>
<li><a href=\"/bulkorder\">bulk order</a></li>
</ul>' WHERE identifier = 'footer-section-4'");
        }
        $setup->endSetup();
    }
}
