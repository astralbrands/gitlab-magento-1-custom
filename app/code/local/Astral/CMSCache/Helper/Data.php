<?php
/**
 * Class Astral_CMSCache_Helper_Data
 */
class Astral_CMSCache_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_ASTRAL_CMSCACHE_ENABLED = 'system/astral_cmscache/enabled';
 	const XML_PATH_ASTRAL_CMSCACHE_TIMEOUT = 'system/astral_cmscache/timeout';
	const NUM_LETTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    /**
     * @param int $length
     * @return string
     */
    public function randomString($length = 5)
	{
		$characters = self::NUM_LETTERS;
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
		    $randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}
    /**
     * @return mixed
     */
    public function isEnabled()
	{
		return Mage::getStoreConfig(self::XML_PATH_ASTRAL_CMSCACHE_ENABLED);
	}
    /**
     * @return mixed
     */
    public function getCacheTimeout()
	{
		return Mage::getStoreConfig(self::XML_PATH_ASTRAL_CMSCACHE_TIMEOUT);
	}
}