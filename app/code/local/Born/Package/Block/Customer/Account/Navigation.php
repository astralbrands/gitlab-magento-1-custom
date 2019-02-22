<?php

class Born_Package_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    public function loadCustomizations()
    {
        //Remove extra links from account nav
        $removeLinks = array('address_book', 'billing_agreements',
            'recurring_profiles', 'reviews', 'downloadable_products',
            'tags', 'account_edit', 'OAuth Customer Tokens');

        $this->massRemoveLinksByNames($removeLinks);

        //Arrange link order
        $this->placeAfterByName('sagepaymentscards', 'account');
        $this->placeAfterByName('customertickets', 'orders');
        $this->placeAfterByName('newsletter', 'customertickets');

        //Change Names
        $this->changeLabelByName('sagepaymentscards', 'Payment Methods');
        $this->changeLabelByName('newsletter', 'Email Preferences');
        $this->changeLabelByName('account', 'My Information');
        $this->changeLabelByName('wishlist', 'My Favorites');
        $this->changeLabelByName('amxnotif.stock', 'In Stock Notifications');
        $this->changeLabelByName('amxnotif.price', 'Price Notifications');
        //$this->changeLabelByName('aw_sarp2', 'Auto Replenishment');
        $this->changeLabelByName('enterprise_reward', 'My Rewards');
        //Place holder links
        // $this->AddLink('stocknotification', '#', 'In Stock Notifications');

        //add sign out
        $this->AddLink('signout', 'customer/account/logout', $this->__('Sign Out'));
    }

    /**
     * Removes link by name
     *
     * @param string $name
     * @return Mage_Page_Block_Template_Links
     */
    public function removeLinkByName($name)
    {
         unset($this->_links[$name]);
    }

    public function massRemoveLinksByNames($nameArray)
    {
        foreach ($nameArray as $name) {
            $this->removeLinkByName($name);
        }
    }
    
    public function changeLabelByName($name, $label) {
        //echo array_search('address_book',array_keys($this->_links)); exit;
        if(isset($this->_links[$name])){
            $this->_links[$name]->setLabel($label);
        }
    }

    public function placeAfterByName($name, $placeAfter) {

        if(array_key_exists($name,$this->_links) && $this->_links[$name] && array_key_exists($placeAfter,$this->_links) && $this->_links[$placeAfter]){
            $currentIndex = array_search($name,array_keys($this->_links));
            $placementIndex = array_search($placeAfter,array_keys($this->_links));

            $links = $this->_links;
            $links = array_values($links);
            if($currentIndex < $placementIndex) {
                while($currentIndex < $placementIndex) {
                    $temp = $links[$currentIndex];
                    $links[$currentIndex] = $links[$currentIndex+1];
                    $links[$currentIndex+1] = $temp;
                    $currentIndex++;
                }
            } else {
                while($currentIndex > $placementIndex + 1) {
                    $temp = $links[$currentIndex];
                    $links[$currentIndex] = $links[$currentIndex-1];
                    $links[$currentIndex-1] = $temp;
                    $currentIndex--;
                }
            }

            $this->_links = array();
            foreach($links as $link) {
                $this->_links[$link->getName()] = $link;
            }
        }
    }

    public function isActive($link)
    {
        if (empty($this->_activeLink)) {
            $this->_activeLink = $this->getAction()->getFullActionName('/');
        }
        if ($this->_completePath($link->getPath()) == $this->_activeLink) {
            return true;
        }
        else{
            $_linkItems = $this->getConfigLinkItems();

            foreach ($_linkItems as $_item) {
                if ($_item['nav_path'] == $link->getPath() && $_item['page_path'] == $this->_activeLink) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function getConfigLinkItems()
    {   
        //array['nav_path'], array['page_path']
        $_linkItems = Mage::getStoreConfig('customer/account_page_link/link_items', Mage::app()->getStore());
        $_linkItems = unserialize($_linkItems);

        return $_linkItems;
    }
}    