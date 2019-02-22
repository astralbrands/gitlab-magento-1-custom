<?php
		/** To copy B2B store configuration to new store*/
        $store = Mage::getModel('core/store');
        $store->load('cosb2bint_store', 'code');
        $storeId = $store->getId();
        $websiteId = $store->getWebsiteId();

        /* initiate installer */
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        /* start setup */
        $installer->startSetup();
        /* do setup */
        $installer->run("

            # DEFINE
            SET @to_website := $websiteId; # the websites_id of the recipient store
            SET @to_store := $storeId; # the store_id of the recipient store
            SET @from_website := 3 ; # the websites_id of the donor store
            SET @from_store := 3; # the store_id of the donor store

            /** core_config_data */
            DELETE FROM core_config_data
            WHERE scope = 'websites'
            AND scope_id = @to_website;
            DELETE FROM core_config_data
            WHERE scope = 'stores'
            AND scope_id = @to_store;

            INSERT INTO core_config_data (
            scope,
            scope_id,
            path,
            value
            ) SELECT
            scope,
            @to_website,
            path,
            value
            FROM core_config_data
            WHERE scope = 'websites'
            AND scope_id = @from_website;

            INSERT INTO core_config_data (
            scope,
            scope_id,
            path,
            value
            ) SELECT
            scope,
            @to_store,
            path,
            value
            FROM core_config_data
            WHERE scope = 'stores'
            AND scope_id = @from_store;
            ");
        /* end setup */
        $installer->endSetup();
?>