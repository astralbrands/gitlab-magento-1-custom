<?php 
class Born_Package_Block_Catalog_Product_View_Tierpricing extends Mage_Catalog_Block_Product_View 
{
	/**
	 * @deprecated
	 */
	public function showTierPricingGuide()
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$_model = Mage::getModel('born_package/catalog_product_view_data');
		$_product = $this->getProduct();
		$_attribute = $_model->getSubscriptionRowByProductId($_product->getId());

		if (!empty($_attribute)) {
			return true;
		}

		return false;
	}


	public function getTierPricingBlockHtml($_tierPrices)
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$_product = $this->getProduct();

		$_qtyRanges = $this->getRangeArray($_tierPrices);
		$_html = '';

		$index = 0;

		foreach ($_tierPrices as $_price) 
		{
			if ($index == 0 && $_price['price_qty'] > 1) {
				$_html .= '<div class="tier-price-inner index-' . $index .'">';
				$_html .= '<span class="quantity-range">' . $_qtyRanges[$index] .'</span>';
				$_html .= $this->getPriceHtml($_product);
				$_html .= '</div>';
				$index++;

			}
			$_html .= '<div class="tier-price-inner index-' . $index .'">';
			$_html .= '<span class="quantity-range">' . $_qtyRanges[$index] .'</span>';
			$_html .= '<div class="price-box">';
			$_html .= '<span class="regular-price" id="product-price-'. $_product->getId() .'">';
			$_html .= $_price['formated_price'];
			$_html .= '</span>';
        	$_html .= '</div>';
        	$_html .= '</div>';

        	$index++;

		}
		return $_html;
	}


	public function getRangeArray($_tierPrices)
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$_ranges = array();
		$_priceQtys = array();

		$index = 0;
		foreach ($_tierPrices as $key => $price) {
			$_priceQty = $price['price_qty'];
			if ($index++ == 0 && $_priceQty > 1) {
				$_priceQtys[] = 1;	
			}

			$_priceQtys[] = $_priceQty;
		}

		for($i = 0; $i < sizeof($_priceQtys); $i++)
		{
			if (isset($_priceQtys[$i+1])) {
				$_ranges[$i] = $_priceQtys[$i] . ' - ' . ((int)$_priceQtys[$i+1] - 1);
			}
			else{
				$_ranges[$i] = $_priceQtys[$i] . '+';
			}
		}

		return $_ranges;
	}
}