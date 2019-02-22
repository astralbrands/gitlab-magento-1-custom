<?php
		/** To copy B2B category permission configuration to new store*/
        $store = Mage::getModel('core/store');
        $store->load('cosb2bint_store', 'code');
        $storeId = $store->getId();
        $websiteId = $store->getWebsiteId();
		/* initiate installer */
		$installer = $this;

		/* start setup */
		$installer->startSetup();
		/* do setup */
		$installer->run("

		# DEFINE
		SET @to_website := $websiteId; # the websites_id of the recipient store
		SET @to_store := $storeId; # the store_id of the recipient store
		SET @from_website := 3; # the websites_id of the donor store
		SET @from_store := 3; # the store_id of the donor store
		 
		/** enterprise_catalogpermissions */
		DELETE FROM enterprise_catalogpermissions
		WHERE website_id = @to_website;
		 
		INSERT INTO enterprise_catalogpermissions (
			category_id,
			website_id,
			customer_group_id,
			grant_catalog_category_view,
			grant_catalog_product_price,
			grant_checkout_items
		) SELECT
			category_id,
			@to_website,
			customer_group_id,
			grant_catalog_category_view,
			grant_catalog_product_price,
			grant_checkout_items
		FROM enterprise_catalogpermissions
		WHERE website_id = @from_website;
		");
		 
		/* end setup */
		$installer->endSetup();
?>