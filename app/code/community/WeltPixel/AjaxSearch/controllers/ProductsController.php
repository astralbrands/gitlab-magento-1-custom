<?php

class WeltPixel_AjaxSearch_ProductsController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $query = Mage::helper('catalogsearch')->getQuery();

        $query->setStoreId(Mage::app()->getStore()->getId());
        if ($query->getId()) {
            $query->setPopularity($query->getPopularity() + 1);
        } else {
            $query->setPopularity(1);
        }

        $query->prepare();
        if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
            $query->save();
        }

        $ajaxHelper = Mage::helper('weltpixel_ajaxsearch');

        $moreResultMessage = $ajaxHelper->getMoreResultMessage();
        $maxShownResult = $ajaxHelper->getMaxNrOfResults();
        $imageHeight = $ajaxHelper->getImageHeight();
        $imageWidth = $ajaxHelper->getImageWidth();
        $noResultText = $ajaxHelper->getNoSearchResultText();
        $showShortDescription = $ajaxHelper->showShortDescription();
        $showPrice = $ajaxHelper->showPrice();
        $showThumbnail = $ajaxHelper->showImageThumbnail();
        
        $searchResult = array(
            'resultcounts' => 0,
            'elements' => ''
        );

        /** @var Mage_CatalogSearch_Model_Fulltext $preparedResult */
        $preparedResult = Mage::getSingleton('catalogsearch/fulltext');
        $preparedResult->prepareResult();
        $foundData = $preparedResult->getResource()->getFoundData();
        $productIds = array_keys($foundData);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id', array('in' => $productIds));

        $collection->setStore(Mage::app()->getStore());
        $collection->addStoreFilter();
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->addUrlRewrite();
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        $collection->addAttributeToSelect(array('name', 'short_description', 'thumbnail'));
        
        $resultCount = $collection->getSize();
        $checkOutMore = '';
        if ($maxShownResult) {
            $collection->setPageSize($maxShownResult)->setCurPage(1);
            if ($resultCount > $maxShownResult) {
                $checkOutMore = $moreResultMessage;
            }
        }

        $collection->load();

        if (!$resultCount) {
            $searchResult['elements'] = '<p class="no_result">' . $noResultText . '</p>';
        } else {
            $result = '';
            $searchResult['resultcounts'] = $resultCount;
            foreach ($collection as $product) {
                $thumbnailImage = '';
                if ($showThumbnail) {                    
                    $image = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize($imageWidth, $imageHeight);
                    $thumbnailImage = '<img width="' . $imageWidth . '" height="' . $imageHeight . '" src="' . $image . '" />';
                }
                $shortDescription = '';
                if ($showShortDescription) {
                    $descrLimit = $ajaxHelper->getShortDescriptionLimit();
                    $shortDescription = $product->getShortDescription();
                    if ($descrLimit && strlen($shortDescription) > $descrLimit) {
                        $shortDescription = substr($shortDescription, 0, $descrLimit) . '...';
                    }
                    $shortDescription = '<span class="short_descr">' . $shortDescription . '</span>';
                }

                $price = '';
                if ($showPrice) {
                    $formattedPrice = Mage::helper('core')->currency($product->getFinalPrice(), true, false);
                    $price = '<span class="show_price">' . $formattedPrice . '</span>';
                }


                $result .= '<p class="search_item"><a href="' . $product->getProductUrl() . '">'. $thumbnailImage .'<span class="item_name">' . $product->getName() . '</span>' . $shortDescription . $price . '</a></p>';
            }

            $result .= $checkOutMore;
            
            $result .= '<script> if ($("advanced_search")) { $("advanced_search").observe("click", function(event) {  $("wpas-form").submit(); }); }</script>';

            $searchResult['elements'] = $result;
        }

        $this->_sendResult($searchResult);
    }

    private function _sendResult($result) {
        header('Content-Type: application/x-json; charset=utf-8');
        echo Zend_Json::encode($result);
    }

}
