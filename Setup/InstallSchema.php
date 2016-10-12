<?php

namespace Magebuzz\Events\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
                ->newTable($installer->getTable('mb_events'))
                ->addColumn(
                        'event_id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Event ID'
                )
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Event Title')
                ->addColumn('description', Table::TYPE_TEXT, null, [], 'Description')
                ->addColumn('image', Table::TYPE_TEXT, 255, ['nullable' => false], 'Image')
                ->addColumn('number_of_participant', Table::TYPE_INTEGER, 11, ['unsigned' => true], 'Number of Participant')
                ->addColumn('allow_register', Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => '0'], 'Allow Register')
                ->addColumn('registration_deadline', Table::TYPE_DATETIME, null, [], 'Registration Deadline')
                ->addColumn('cost', Table::TYPE_DECIMAL, '12,4', [], 'Cost')
                ->addColumn('status', Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => '0'], 'Status')
                ->addColumn('store_id', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => '0'], 'Store Id')
                ->addColumn('created_time', Table::TYPE_DATETIME, null, [], 'Created Time')
                ->addColumn('start_time', Table::TYPE_DATETIME, null, [], 'Start Time')
                ->addColumn('end_time', Table::TYPE_DATETIME, null, [], 'End Time')
                ->addIndex(
                        $installer->getIdxName('mb_events', ['store_id']), ['store_id']
                )
                ->setComment('Events');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
                ->newTable($installer->getTable('mb_categories'))
                ->addColumn(
                        'category_id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Category ID'
                )
                ->addColumn('category_title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Category Title')
                ->addColumn('category_description', Table::TYPE_TEXT, null, ['nullable' => false], 'Description')
                ->addColumn('status', Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => '0'], 'Status')
                ->addColumn('store_id', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => '0'], 'Store Id')
                ->addColumn('created_time', Table::TYPE_DATETIME, null, [], 'Created Time')
                ->addIndex(
                        $installer->getIdxName('mb_categories', ['store_id']), ['store_id']
                )
                ->setComment('Categories');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
                ->newTable($installer->getTable('mb_event_category'))
                ->addColumn(
                        'event_category_id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Category ID'
                )
                ->addColumn('category_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'Store Id')
                ->addColumn('event_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'Store Id')
                ->addIndex(
                        $installer->getIdxName('mb_event_category', ['category_id']), ['category_id']
                )
                ->addIndex(
                        $installer->getIdxName('mb_event_category', ['event_id']), ['event_id']
                )
                ->addForeignKey(
                        $installer->getFkName('mb_event_category', 'category_id', 'mb_categories', 'category_id'), 
                        'category_id', $installer->getTable('mb_categories'), 'category_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                        $installer->getFkName('mb_event_category', 'event_id', 'mb_events', 'event_id'),
                        'event_id', $installer->getTable('mb_events'), 'event_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Events Categories');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
