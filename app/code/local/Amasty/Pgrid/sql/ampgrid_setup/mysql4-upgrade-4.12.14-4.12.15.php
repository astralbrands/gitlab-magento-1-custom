<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->truncateTable($installer->getTable('ampgrid/qty_sold'));

$installer->getConnection()->addIndex(
    $installer->getTable('ampgrid/qty_sold'),
    $installer->getIdxName(
        'ampgrid/qty_sold',
        array('product_id')
    ),
    array('product_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
$installer->getConnection()->closeConnection();
