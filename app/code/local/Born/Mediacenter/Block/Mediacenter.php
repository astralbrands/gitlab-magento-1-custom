<?php

/**
 * Class Born_Mediacenter_Block_Mediacenter
 */
class Born_Mediacenter_Block_Mediacenter extends Mage_Core_Block_Template
{
	public function getCustomerGroupId(){
		$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		return $groupId;
	}
	public function getSections() {
		$sections = Mage::getModel('mediacenter/mediacenter')->getCollection()->addFieldToFilter('customer_group',array('finset' => $this->getCustomerGroupId()));
		return $sections;
	}
	public function getSubSections($ids) {
		$subsections = Mage::getModel('mediacenter/subsections')->getCollection()->addFieldToFilter('entity_id',array('in'=>$ids))->addFieldToFilter('customer_group',array('finset' => $this->getCustomerGroupId()));
		return $subsections;
	}
	public function getMediaInfo($parentId) {
		$media = Mage::getModel('mediacenter/images')->getCollection()->addFieldToFilter('parent_id',$parentId)->addFieldToFilter('media_customer_group',array('finset' => $this->getCustomerGroupId()));
		return $media;
	}
}