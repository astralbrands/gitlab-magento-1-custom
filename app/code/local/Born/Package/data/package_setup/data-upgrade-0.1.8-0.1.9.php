<?php
		/* Assign Hooks row information to new store */
        $rows = Mage::getModel('borncmshooks/rows')->getCollection()->addStoreFilter(3);
		$store = Mage::getModel('core/store')->load('cosb2bint_store', 'code');
		$newstoreId = $store->getId();
		foreach ($rows as $row) {
			$row = Mage::getModel('borncmshooks/rows')->load($row->getId());
			$storeId = $row->getStoreId();
			$storeId .= ',' . $newstoreId;
			$row->setStoreId($storeId);
			$row->save();
		}

?>