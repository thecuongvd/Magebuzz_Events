<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

//        if (version_compare($context->getVersion(), '1.1.0', '<')) {
//            $installer->getConnection();
//            $installer->getConnection()->addColumn(
//                    $installer->getTable('mb_bannermanager_block'), 'min_images', [
//                'type' => Table::TYPE_INTEGER,
//                'unsigned' => true,
//                'nullable' => false,
//                'default' => '1',
//                'comment' => 'Min Images'
//                    ]
//            );
//        }
        $setup->endSetup();
    }

}
