<?php 

class Born_Package_Block_Bundle_Checkout_Cart_Item_Renderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
	/**
	 * Show Size and Shade attribute for simple products
	 * @return array
	 */
	protected function getCustomAttributes()
	{
		$_product = $this->getItem()->getProduct();

		if($_product->getTypeId() == 'simple'){
			$_customAttributeCodes= array('shade', 'size');

			$_customAttributesValues = array();

			$_product = Mage::getModel('catalog/product')->load($_product->getId());

			foreach($_customAttributeCodes as $_code){
				$_customAttributesValues[$_code] = $_product->getAttributeText($_code);
			}
			return $_customAttributesValues;
		}

		return;
	}

	public function isFreeItem($message) {
        $amastyConfigMessage = Mage::getStoreConfig('ampromo/general/message',Mage::app()->getStore());
        $price = (int)$this->getItem()->getPrice();

        if($price === 0 && $message === $amastyConfigMessage) {
            return true;
        }

        return false;
    }
}

?>