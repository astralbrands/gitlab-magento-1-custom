<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Helper_Data extends Mage_Core_Helper_Data {

    /**
     * force country code if not resolved from locale
     */
    const XML_PATH_STORELOCATOR_COUNTRY_CODE = 'storelocator/storelocator/country_code';
    const XML_PATH_STORELOCATOR_LANGUAGE = 'storelocator/storelocator/language';

    public function getLanguage() {
        return Mage::getStoreConfig(self::XML_PATH_STORELOCATOR_LANGUAGE);
    }

    /**
     * returns iso2 country code
     *  
     */
    function getCountry() {
        if (Mage::getStoreConfig(self::XML_PATH_STORELOCATOR_COUNTRY_CODE))
            $country = Mage::getStoreConfig(self::XML_PATH_STORELOCATOR_COUNTRY_CODE);
        else {
            $website = Mage::app()->getWebsite()->getCode();
            $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
            $country = strtoupper($locale[1]);
            // $country;
        }

        Mage::getSingleton('storelocator/iso2')->validateCountryCode($country);
        return $country;
    }

    /* function to get store id */

    public function getCurrentStoreId() {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        if (!empty($role_data['gws_stores'])) {
            return $role_data['gws_stores'];
        }
        return false;
    }

    /* function to get payment methods */

    public function getWebsites() {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $methods[$store->getId()] = $store->getName();
                }
            }
        }
        return $methods;
    }

    public function searchCheck() {

        $max_hours = Mage::getStoreConfig('storelocator/search_protection/max_hours');
        $max_count = Mage::getStoreConfig('storelocator/search_protection/max_attempts');
		
        if (!$max_hours) {
            //Mage::log('Max hours not set in config');
            $max_hours = 24;
        }
        if (!$max_count) {
            //Mage::log('Max count not set in config');
            $max_count = 3;
        }

        $count = Mage::getSingleton("core/session")->getStoreSearchCount();
        if ($count) {
            if ($count >= $max_count) {

                //Get Last visited Time Stamp
                $time_stamp = Mage::getSingleton("core/session")->getStoreSearchAccessTime();

                //create a date object with the last visited timestamp
                $last = new DateTime();
                $last->setTimeStamp($time_stamp);

                //get current date and time
                $now = new DateTime();

                //calculated elapsed time in hours
                $diff = $last->diff($now);
                //convert days,months,years to hours
                $elapsed_hours = ($diff->y * 360 * 24) + ($diff->m * 30) + ($diff->d * 24) + $diff->h;
                if ($elapsed_hours > $max_hours) {
                    $this->initializeCount();
                    return true;
                } else {
                    $count+=1;
                    Mage::getSingleton("core/session")->setStoreSearchCount($count);
                    Mage::getModel('core/cookie')->set('count', $count, 86400);
                    return false;
                }
            } else {
                //count is less than 3
                $count+=1;
                Mage::getSingleton("core/session")->setStoreSearchCount($count);
                Mage::getModel('core/cookie')->set('count', $count, 86400);
                return true;
            }
        } else {
            $this->initializeCount();
            return true;
        }
    }

    private function initializeCount() {
        $now = new DateTime();
        Mage::getSingleton("core/session")->setStoreSearchAccessTime($now->getTimeStamp());
        Mage::getSingleton("core/session")->setStoreSearchCount(1);
        Mage::getModel('core/cookie')->set('count', 1, 86400);
        Mage::getModel('core/cookie')->set('StoreSearchAccessTime', $now->getTimeStamp(), 86400);
    }

    public function getErrorMsg() {

        $msg = Mage::getStoreConfig('storelocator/search_protection/err_msg');
        if (!$msg) {
            $msg = $this->__('The search cannot be used more than 3 times within 24 hours. Please try again later.');
        }

        return $this->__($msg);
    }

    public function getStoreOptions() {
        $store_ids = $this->getCurrentStoreId();
        $groups = Mage::getModel('core/store_group')
                ->getCollection();
        $groups->addFieldToFilter('default_store_id', array('in' => $store_ids));
        $options = array();
        foreach ($groups as $g) {
            $options[$g->getDefaultStoreId()] = $g->getName();
        }
        return $options;
    }

}
