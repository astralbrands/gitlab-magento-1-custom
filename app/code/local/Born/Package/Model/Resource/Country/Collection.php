<?php
class Born_Package_Model_Resource_Country_Collection extends Mage_Directory_Model_Resource_Country_Collection
{
    public function loadByStore($store = null)
    {
        $allowCountries = Mage::helper('born_package/country')->getAllowedCountriesByCustomerGroup($store);
        if (!$allowCountries)
            $allowCountries = explode(',', (string)$this->_getStoreConfig('general/country/allow', $store));
        if (!empty($allowCountries)) {
            $this->addFieldToFilter("country_id", array('in' => $allowCountries));
        }
        return $this;
    }


}

?>