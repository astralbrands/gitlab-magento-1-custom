<?php
/**
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2011 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run(
    "  
DELETE FROM {$this->getTable('listrak/product_attribute_set_map')}
WHERE NOT EXISTS(
    SELECT * FROM {$this->getTable('eav/attribute_set')} AS eav_sets
    WHERE eav_sets.attribute_set_id = {$this->getTable('listrak/product_attribute_set_map')}.attribute_set_id
);
    
ALTER TABLE {$this->getTable('listrak/product_attribute_set_map')}
ADD CONSTRAINT fk_attribute_set_id_constraint FOREIGN KEY fk_attribute_set_id (attribute_set_id)
REFERENCES {$this->getTable('eav/attribute_set')} (attribute_set_id)
ON DELETE CASCADE;
    ");

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.2.0-1.2.1 update");
} catch (Exception $ex) {
}

$installer->endSetup();