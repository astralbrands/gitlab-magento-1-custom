<?php
class Born_Borncmshooks_Block_Borncmshooksbanner extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function testing(){
        Zend_Debug::dump(now());
    }
    
    public function getBorncmshooks()     
    { 
        if (!$this->hasData('borncmshooks')) {
            $this->setData('borncmshooks', Mage::registry('borncmshooks'));
        }
        return $this->getData('borncmshooks');
    }
    
    public function getCMSBannerHtml($bannerName = '', $bannerData = null)
    {

    	$blockDataName = 'banners';
    	if($bannerName == 'slider'){
    		$blockDataName = 'slides';
    	}
    	if($bannerName && $bannerData){
    		$templateName = str_replace('_','-',$bannerName);
    		if($this->getChild($bannerName)){
				if($this->getChild($bannerName)->getTemplate() == NULL){
					$this->getChild($bannerName)->setTemplate('borncmshookslayouts/blocks/' . $templateName . '.phtml')->setData($blockDataName,$bannerData);
				}else{
					$this->getChild($bannerName)->setData($blockDataName, $bannerData);
				}
				return $this->getChildHtml($bannerName);
			}
    	}
    	
    	return '';
    }
    
    public function getCMSBannerHeaderHtml($headerData = null)
    {
    	$currentBlockName = $this->getNameInLayout();
	    if($this->getChild('header')){
	    	$templateName = str_replace('_','-',$currentBlockName);
			if($this->getChild('header')->getTemplate() == NULL){
				$this->getChild('header')->setTemplate('borncmshookslayouts/blocks/' . $templateName . '-header.phtml')->setData('header', $headerData);
			}else{
				$this->getChild('header')->setData('header', $headerData);
			}
			return $this->getChildHtml('header');
		}
		
		return '';
    }
    
    public function getCMSChildBannerHtml($bannerName = '', $bannerData = null, $bannerCounter = 0)
    {
    	if ($bannerName && $bannerData){
	    	if($this->getChild('banner_'.$bannerCounter)){
	            if($this->getChild('banner_'.$bannerCounter)->getTemplate() == NULL){
	                $this->getChild('banner_'.$bannerCounter)->setTemplate('borncmshookslayouts/blocks/' . $bannerName . '.phtml')->setData('banner', $bannerData);
	            }else{
	                $this->getChild('banner_'.$bannerCounter)->setData('banner', $bannerData);
	            }
	            return $this->getChildHtml('banner_'.$bannerCounter);
	        }
    	}
    	return '';
    }
    
}