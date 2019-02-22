<?php
/**
 * @author Astral Brands Team
 * @package Astral_Integrations
 */


class Astral_Integrations_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Returns the Id for Attentive
     * @return string
     */
    public function getAttentiveId() {
        return Mage::getStoreConfig('astral_integrations_admin/attentive/attentive_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for Commission Junction
     * @return string
     */
    public function getCommissionJunctionId() {
        return Mage::getStoreConfig('astral_integrations_admin/commission_junction/comission_junction_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for Criteo
     * @return string
     */
    public function getCriteoId() {
        return Mage::getStoreConfig('astral_integrations_admin/criteo/criteo_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for Facebook Pixel
     * @return string
     */
    public function getFacebookPixelId() {
        return Mage::getStoreConfig('astral_integrations_admin/facebook_pixel/fbq_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for commission Junction
     * @return string
     */
    public function getHotjarId() {
        return Mage::getStoreConfig('astral_integrations_admin/hotjar/hotjar_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for Mavrck
     * @return string
     */
    public function getMavrckId() {
        return Mage::getStoreConfig('astral_integrations_admin/mavrck/mavrck_id', Mage::app()->getStore());
    }

    /**
     * Returns the Id for Steelhouse
     * @return string
     */
    public function getSteelhouseId() {
        return Mage::getStoreConfig('astral_integrations_admin/steelhouse/steelhouse_id', Mage::app()->getStore());
    }

    public function recursiveIsEmpty($array) {
        $isEmpty = true;
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $subarray) {
                if ($isEmpty) {
                    $isEmpty = $isEmpty && $this->recursiveIsEmpty($subarray);
                } else {
                    break;
                }
            }
        } else {
            $isEmpty = empty($array);
        }
        return $isEmpty;
    }

}
