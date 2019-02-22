<?php
        //#addWebsite
            $website = Mage::getModel('core/website');
            $website->setCode('cosb2bint')
                ->setName('Cosmedix International Distributors')
                ->save();
        //#addStoreGroup
            $storeGroup = Mage::getModel('core/store_group');
            $storeGroup->setWebsiteId($website->getId())
                ->setName('COSMEDIX Website')
                ->setRootCategoryId(3)
                ->save();
        //#addStore
            $store = Mage::getModel('core/store');
            $store->setCode('cosb2bint_store')
                ->setWebsiteId($storeGroup->getWebsiteId())
                ->setGroupId($storeGroup->getId())
                ->setName('COSMEDIX B2B INT Store View')
                ->setIsActive(1)
                ->save();

?>