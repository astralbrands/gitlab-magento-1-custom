<?php

$installer = $this;

$installer
    ->getConnection()
    ->addConstraint(
        'FK_ITEMS_RELATION_ITEM',
        $installer->getTable('weltpixel_attributeimages/attributeimages'), 
        'attribute_id',
        $installer->getTable('eav/attribute'), 
        'attribute_id',
        'cascade', 
        'cascade'
);

$installer->endSetup();
