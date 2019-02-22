<?php

class Born_Package_Helper_Wishlist extends Mage_Wishlist_Helper_Data {
    public function getFormKey() {
        return $this->_getSingletonModel('core/session')->getFormKey();
    }
}