<?php

namespace Magebuzz\Events\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $fieldList = [
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'minimal_price',
            'cost',
            'tier_price',
            'weight',
            'tax_class_id'
        ];
        foreach ($fieldList as $field) {
            $applyTo = explode(
                    ',', $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array('event', $applyTo)) {
                $applyTo[] = 'event';
                $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to', implode(',', $applyTo)
                );
            }
        }

    }

}
