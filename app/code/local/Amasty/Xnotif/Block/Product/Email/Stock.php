<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Block_Product_Email_Stock extends Mage_ProductAlert_Block_Email_Stock
{
    /**
     * Get store url params
     *
     * @return string
     */
    protected function _getUrlParams()
    {
        $data =  array(
            '_store'        => $this->getStore(),
            '_store_to_url' => true
        );
        if ($this->getCustomer() && !$this->getCustomer()->getId()) {
            $salt = Amasty_Xnotif_Helper_Data::SALT;
            $email = $this->getCustomer()->getEmail();

            $data['customer_email'] = urlencode($email);
            $data['hash'] = urlencode(md5($email . $salt));
        }

        return $data;
    }
}