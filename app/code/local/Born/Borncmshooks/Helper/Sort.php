<?php 
class Born_Borncmshooks_Helper_Sort extends Mage_Core_Helper_Abstract
{
	public function sortCollection($object_collection, $sortAttribute="sort_order")
	{
		$tempCollections = array();

		foreach ($object_collection as $object) {
			if($sortOrder = $object->getData($sortAttribute)){
				$tempCollections[$sortOrder][] = $object;
			}else{
				$tempCollections[][] = $object;
			}
		}

		ksort($tempCollections);

		$sorted_data = new Varien_Data_Collection();

		foreach ($tempCollections as $collection){
			foreach ($collection as $object) {
				$sorted_data->addItem($object);
			}
		}
		return $sorted_data;
	}
}

?>