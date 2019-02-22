<?php 
class Astral_Optionswatch_Helper_Data extends Mage_Core_Helper_Abstract
{	
    const MEDIA_PATH = 'swatch';
	public function getProductHoverImage($product, $width = null, $height = null){
            $hoverImageSrc = '';
            $hoverImage =$product->getResource()->getAttribute('hover_image')->getFrontend()->getValue($product);
                if(!!$product->getData('hover_image') && $hoverImage != 'no_selection'){
                    if(!$width ||!$height){
                    $hoverImageSrc = Mage::helper('catalog/image')->init($product, 'hover_image')->resize(284,410)->__toString(); 
                    }else{
                    $hoverImageSrc = Mage::helper('catalog/image')->init($product, 'hover_image')->resize($width,$height)->__toString(); 
                    }
                }
            return $hoverImageSrc;
	}
	
	public function getProductColorOptions($product){
            $colorOptions = array();
		if($product->getTypeId() == "configurable"){
                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);		
                $mediaImage = $this->getConfigMediaSmallImages($product); 
                    foreach($productAttributeOptions as $attributeOption){
                        if($attributeOption['attribute_code'] == "color") {
                        $colorOptions['attribute_id'] = $attributeOption['attribute_id'];
                        $colorOptions['options'] = array();
                            foreach($attributeOption['values'] as $key => $value){
                                if(array_key_exists('value_index',$value)){
                                $option = array(
                                        'value_index' => $value['value_index'],
                                        'swatch_image'=> '',
                                        'media_image' => ''						
                                );
                                $swatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($option['value_index']);
                                    if(!!$swatch->getData('image_file')){
                                    $option['swatch_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $swatch->getData('image_file');
                                    }
                                    if(array_key_exists($value['value_index'],$mediaImage) ){
                                    $option['media_image'] = $mediaImage[$value['value_index']];
                                    }
                                }
                                array_push($colorOptions['options'], $option);
                            }
                        break;
                        }		
                    }
		}
            return $colorOptions;
	}

	public function getConfigMediaImages($product, $size=null){
            $sku=$product->getSku();
            $useOpenImage = null;
            $_smallImage = (string)Mage::helper('catalog/image')->init($product, 'image');
                if (strpos($_smallImage, 'off') || strpos($_smallImage, 'open') || strpos($_smallImage, 'capoff.jpg') || strpos($_smallImage, 'open.jpg')) {
                $useOpenImage = true;
                }else{
                $useOpenImage = false;
                }
            $mediaGalleryImages = $product->getData('media_gallery'); 
            $mediaImage = array();
                if(count($mediaGalleryImages['images']) > 0){
                $index = 0;
                    foreach($mediaGalleryImages['images'] as $image){
                    $imageUrl = $this->getGalleryImageUrl($product, $image['file'], $size);							
                        if(!!$image['label']){
                            if(strpos($imageUrl, '-swatch')){
                            $mediaImage[$image['label']]['swatch'] = $imageUrl;
                            }elseif(strpos($imageUrl, '-profile')){
                            $mediaImage[$image['label']]['profile'] = $imageUrl; 
                            $mediaImage[$image['label']]['others-' . $index++] = $imageUrl;
                            }elseif(strpos($imageUrl, '-tone')){
                            $mediaImage[$image['label']]['tone'] = $imageUrl;   
                            $mediaImage[$image['label']]['others-' . $index++] = $imageUrl;
                            }elseif(strpos($imageUrl, '-capon')){
                            $mediaImage[$image['label']]['capon'] = $imageUrl; 
                            $mediaImage[$image['label']]['others-' . $index++] = $imageUrl;
                            }elseif(strpos($imageUrl, '-capoff')){
                            $mediaImage[$image['label']]['capoff'] = $imageUrl; 
                            $mediaImage[$image['label']]['others-' . $index++] = $imageUrl;
                            }elseif(array_key_exists($image['label'],$mediaImage)){
                                if($this->isBaseImage($useOpenImage, $image['file'])){
                                $mediaImage[$image['label']]['base'] = $imageUrl;
                                }
                            }else{
                            $mediaImage[$image['label']]['others-' . $index++] = $imageUrl;
                            }
                        }
                    }
                if ($product->getData('video_image') && $product->getData('video_image') != 'no_selection') {
                $videoUrl = Mage::helper('catalog/image')->init($product, 'image', $product->getData('video_image'))->__toString();
                    foreach ($mediaImage as $key => $image) {
                        if ($videoUrl && array_key_exists('video',$image) && !$image['video']) {
                        $mediaImage[$key]['video'] = $videoUrl;
                        }
                    }
                }
            }
            return $mediaImage;
	}

	protected function getGalleryImageUrl($product, $imageFile, $size){
		if ($imageFile) {
                    $helper = Mage::helper('catalog/image')
                    ->init($product, 'image', $imageFile)
                    ->keepFrame(false);
			if (!$size) {
			$size = Mage::getStoreConfig(Mage_Catalog_Helper_Image::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
			}
			if (is_numeric($size)) {
			$helper->constrainOnly(true)->resize($size);
			}
                    return (string)$helper;
		}
            return null;
	}

	protected function isBaseImage($useOpenImage, $imageUrl){
		if ($useOpenImage) {
                    if ((strpos($imageUrl, 'off') || strpos($imageUrl, 'open'))) {
                    return true;
                    }
                return false;
		}else{
                    if ((strpos($imageUrl, 'closed') || strpos($imageUrl, 'on'))) {
                    return true;
                    }
                return false;
		}
	}

	public function getConfigMediaSmallImages($product){
            $mediaGalleryImages = $product->getMediaGalleryImages(); 
            $mediaImage = array();
                if(count($mediaGalleryImages) > 0){
                    foreach($mediaGalleryImages as $image){
                        if(!!$image->getData('label') && !array_key_exists($image->getData('label'),$mediaImage)){
                        $mediaImage[$image->getData('label')] = Mage::helper('catalog/image')->init($product, 'small_image', $image->getFile())->resize(284,410)->__toString();
                        }
                    }
                }
            return $mediaImage;			
	}

	public function getProductImage($product, $base_image, $width, $height, $attribute=null){
            $mediaGalleryImages = $product->getMediaGalleryImages(); 		
            $mediaImage = "";
		if ($attribute) {
		$filterColor = Mage::app()->getRequest()->getParam($attribute);
		}else{
		$filterColor = Mage::app()->getRequest()->getParam('color');	
		}
		if(count($mediaGalleryImages) > 0){
                    foreach($mediaGalleryImages as $image){				
                        if($filterColor == $image->getData('label')){
                        $mediaImage = Mage::helper('catalog/image')->init($product, 'small_image', $image->getFile())->resize($width,$height)->__toString();
                        }
                    }
		}		
            $_image = (trim($mediaImage)) == ''?$base_image:$mediaImage;
            return $_image;
	}
}