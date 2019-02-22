<?php
		/** To copy B2B category configuration to new store*/
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
		 
		/** catalog_category_entity_int */
		DELETE FROM catalog_category_entity_int
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_int (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_int
		WHERE store_id = @from_store;
		 
		/** catalog_category_entity_text */
		DELETE FROM catalog_category_entity_text
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_text (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_text
		WHERE store_id = @from_store;
		 
		/** catalog_category_entity_varchar */
		DELETE FROM catalog_category_entity_varchar
		WHERE store_id = @to_store;
		 
		INSERT INTO catalog_category_entity_varchar (
			entity_type_id,
			attribute_id,
			store_id,
			entity_id,
			value
		) SELECT
			entity_type_id,
			attribute_id,
			@to_store,
			entity_id,
			value
		FROM catalog_category_entity_varchar
		WHERE store_id = @from_store;
		");
		 
		/* end setup */
		$installer->endSetup();
?>