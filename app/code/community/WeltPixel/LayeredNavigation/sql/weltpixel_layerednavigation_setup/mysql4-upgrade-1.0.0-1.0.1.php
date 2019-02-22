<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->getConnection()->addColumn(
        $installer->getTable('catalog/eav_attribute'), 'enable_layered_swatch', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable' => true,
    'default'   => '0',
    'comment' => 'Enable swatch in layered navigation'
        )
);

$installer->endSetup();
