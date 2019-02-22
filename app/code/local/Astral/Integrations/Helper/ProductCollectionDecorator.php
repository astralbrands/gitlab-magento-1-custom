<?php
/**
 * @author Astral Brands Team
 * @package Astral_Integrations
 */


class Astral_Integrations_Helper_ProductCollectionDecorator extends Mage_Core_Helper_Abstract {

    /**
     * Returns the array used for the Criteo View List Event
     * @param  [Obj] $productCollection [description]
     * @return [array]
     */
    public function getCriteoViewListEventArray($productCollection) {
        $criteoViewList = array();
        if(isset($productCollection) && !empty($productCollection)) {
            $criteoViewList['event'] = 'viewList';
            if(isset($productCollection) && !empty($productCollection)) {
                $i = 0;
                foreach($productCollection as $product) {
                    $criteoViewList['item'][] = $product->getSku();
                    //Stop Reporting at 5 items
                    if ($i > 3) {
                        break;
                    }
                    $i++;
                }
            }

        }
        return $criteoViewList;
    }
}
