<?php

/**
 * Astal Brand
 *
 * @category    Astal Brand
 * @package     Born_Package
 * @description creating the ticket and processing the data.
 */
class Born_Package_Helper_Zendesk_Data extends Mage_Core_Helper_Abstract
{
    public function getBrandId()
    {
        $_storeId = Mage::app()->getStore()->getStoreId();
        $_path = 'zendesk/general/brand_id';
        $_config = Mage::getStoreConfig($_path, $_storeId);

        return $_config;
    }

    public function prepareTicketBody($postData)
    {
        $_ticketBody = '';

        foreach ($postData as $key => $value) {
            if (!(strpos($key, 'hide') === false)) {
                continue;
            }

            $value = $this->processFormValue($value);

            $_fieldName = $this->prepareFieldName($key);
            $_ticketBody .= '<p>' . '<b>' . $_fieldName . ':</b> ';
            $_ticketBody .= $value . '</p>';
        }

        return $_ticketBody;
    }

    public function prepareFieldName($key)
    {
        $key = explode('_', $key);

        $_tempStorage = array();

        foreach ($key as $word) {
            $_tempStorage[] = ucfirst($word);
        }

        $key = implode(' ', $_tempStorage);

        // $key = str_replace('_', ' ', $key);
        //
        return $key;
    }

    public function getFormName()
    {
        $path = 'zendesk/general/form_name';
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_config = Mage::getStoreConfig($path, $_storeId);

        return $_config;
    }
    public function getAffiliateFormName()
    {
        $path = 'zendesk/general/affiliate_form_name';
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_config = Mage::getStoreConfig($path, $_storeId);

        return $_config;
    }

    public function processFormValue($data)
    {
        if (is_array($data) && $data) {
            $_index = 0;
            $_arraySize = count($data);
            $_tempData = '';
            foreach ($data as $_value) {
                if ($_value) {
                    if ($_index != 0) {
                        $_tempData .= ', ';
                    }
                    $_tempData .= $_value;
                }
                $_index++;
            }
            $data = $_tempData;
        }
        return $data;
    }

}

?>