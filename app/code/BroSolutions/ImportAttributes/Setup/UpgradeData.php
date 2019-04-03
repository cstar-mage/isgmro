<?php

namespace BroSolutions\ImportAttributes\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $eavAttributeTableName = $setup->getTable('eav_attribute');
            $selectQuery = "SELECT `attribute_id` FROM ".$eavAttributeTableName." WHERE `attribute_code` = 'is_anchor'";
            $isAnchorAttributeId = $setup->getConnection()->fetchOne($selectQuery);
            $categoryEntityIntTableName = $setup->getTable('catalog_category_entity_int');
            $query = "UPDATE `{$categoryEntityIntTableName}` SET `value` = 1 WHERE `attribute_id` = {$isAnchorAttributeId} AND `entity_id` NOT IN (1,2)";
            $setup->getConnection()->query( $query);
        }

        $setup->endSetup();
    }
}