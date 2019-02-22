<?php
	/* To assign CMS pages to new store */
	$store = Mage::getModel('core/store')->load('cosb2bint_store', 'code');
	$collection = Mage::getModel('cms/page')->getCollection()
		->addFieldToFilter('is_active', 1)
		->addStoreFilter(3);
	$newstoreId = $store->getId();
	foreach ($collection as $cmspage) {
		$cmspage = Mage::getModel('cms/page')->load($cmspage->getPageId());
		$storeIds = $cmspage->getStoreId();

		if (in_array(3, $storeIds) && !in_array($newstoreId, $storeIds)) {
			$storeIds[] = $newstoreId;
			$cmspage->setStores($storeIds);
			$cmspage->save();
		}
	}
?>