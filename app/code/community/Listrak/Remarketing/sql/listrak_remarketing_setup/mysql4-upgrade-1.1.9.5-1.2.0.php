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
create index review_update_review_id_index on {$this->getTable('listrak/review_update')} (review_id);
create index review_update_entity_id_index on {$this->getTable('listrak/review_update')} (entity_id);
create index review_update_activity_index on {$this->getTable('listrak/review_update')} (activity);
create index subscriber_update_subscriber_id_index on {$this->getTable('listrak/subscriber_update')} (subscriber_id);
create index subscriber_update_updated_at_index on {$this->getTable('listrak/subscriber_update')} (updated_at);
create index listrak_remarketing_emailcapture_page_index on {$this->getTable('listrak/emailcapture')} (page);
    ");

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.9.5-1.2.0 update");
} catch (Exception $ex) {
}

$installer->endSetup();