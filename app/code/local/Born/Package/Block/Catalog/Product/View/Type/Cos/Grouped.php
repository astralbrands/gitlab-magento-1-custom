<?php 

class Born_Package_Block_Catalog_Product_View_Type_Cos_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{

	protected function getPurpose($_item)
	{
		if ($_purpose = $_item->getGroupPurpose()) {
			return $_purpose;
		}
		elseif($_purpose = $_item->getAttributeText('purpose'))
			if (strpos($_purpose, ',')) {
				$_purpose = explode(',', $_purpose);
			}
			if (is_array($_purpose)) {
				return array_shift($_purpose);
			}

			return $_purpose;
	}	
}

?>