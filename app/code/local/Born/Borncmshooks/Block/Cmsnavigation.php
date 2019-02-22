<?php 
class Born_Borncmshooks_Block_Cmsnavigation extends Born_Borncmshooks_Block_Borncmshooks{

    private $_configPath = array();


    protected function getConfigPath()
    {
        if (!$this->_configPath){
            $this->setConfigPath();
        }

        return $this->_configPath;
    }

    protected function setConfigPath()
    {
        if(!$this->getPaths()){
            $_customerPaths = array(
                "main_title" => "born_package/footer_links_nav/footer_title1",
                "links" => "born_package/footer_links_nav/footer_links1",
                );
            $_companyPaths = array(
                "main_title" => "born_package/footer_links_nav/footer_title2",
                "links" => "born_package/footer_links_nav/footer_links2",
                );

            $_paths = array($_customerPaths, $_companyPaths);

            $storeId = $this->getStoreId();

            foreach ($_paths as $path) {
                $navLinks = unserialize(Mage::getStoreConfig($path['links'], $storeId));
                $_pageIdentifier = $this->getCurrentPageId();

                foreach ($navLinks as $link)
                {
                    if($link['url_key'] == $_pageIdentifier || $link['url_key'] == $this->getCurrentUrlKey()){
                        $this->_configPath = $path;
                    }
                }
            }
        }
    }

    protected function getStoreId()
    {
        return Mage::app()->getStore();
    }
    
    public function getNavMainTitle()
    {
    	$storeId = $this->getStoreId();
        $_configPath = $this->getConfigPath();
		if(array_key_exists('main_title',$_configPath)){
		$navTitle = Mage::getStoreConfig($_configPath['main_title'], $storeId);
		}else{
		$navTitle = '';
		}
		return $navTitle;
    }
    public function getNavLinks()
    {
        $storeId = $this->getStoreId();
        $_configPath = $this->getConfigPath();
	    if(array_key_exists('links',$_configPath)){
	    $navLinks = unserialize(Mage::getStoreConfig($_configPath['links'], $storeId));
	    }else{
	    $navLinks = '';
	    }
        return $navLinks;
    }

    public function getCurrentPageId()
    {
        return Mage::getSingleton('cms/page')->getIdentifier();
    }

    public function getIsActive($urlKey)
    {
    	$_pageIdentifier = $this->getCurrentPageId();

    	if($_pageIdentifier == $urlKey || $urlKey == $this->getCurrentUrlKey()){
    		return 'active';
    	}
	    return;
    }

    protected function getNavUrl($url)
    {
        if ($this->isValidUrl($url)) {
            return $url;
        }
        else
        {
            return $this->getUrl($url);
        }
    }

    protected function isExternalUrl($url)
    {
        $_baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $_domain= parse_url($_baseUrl, PHP_URL_HOST);

        if ($this->isValidUrl($url)) {
            if (strpos($url, $_domain)) {
                return false;
            }else{
                return true;
            }
        }
        return;
    }

    protected function isValidUrl($url) 
    { 
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}

 ?>