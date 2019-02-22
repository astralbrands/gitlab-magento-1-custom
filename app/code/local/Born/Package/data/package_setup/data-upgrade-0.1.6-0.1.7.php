<?php
	/* To assign blocks to new store */
	$store = Mage::getModel('core/store')->load('cosb2bint_store', 'code');
	$collection = Mage::getModel('cms/block')->getCollection()
		->addFieldToFilter('is_active', 1)
		->addStoreFilter(3);
	$newstoreId = $store->getId();
	foreach ($collection as $block) {
		$block = Mage::getModel('cms/block')->load($block->getBlockId());
		$storeIds = $block->getResource()->lookupStoreIds($block->getBlockId());
		if (in_array(3, $storeIds) && !in_array($newstoreId, $storeIds)) {
			$storeIds[] = $newstoreId;
			$block->setStores($storeIds);
			$block->save();
		}
	}
?>