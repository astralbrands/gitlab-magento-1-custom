<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->getConnection()->addColumn(
        $installer->getTable('catalog/eav_attribute'), 'layered_filter_option', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => true,
    'default'   => '-1',
    'comment' => 'Attribute options on layred navigation'
        )
);

$installer->endSetup();
