<?php 
class Born_Package_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
 public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }
            foreach ($this->getCustomer()->getAddresses() as $address) {
                if($address->getId() == $addressId || !Mage::helper('born_b2baccess')->isActionAllowed()){
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                    );
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'_address_id')
            ->setId($type.'-address-select')
            ->setClass('address-select')
            ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
            ->setValue($addressId)
            ->setOptions($options);

            if (Mage::helper('born_b2baccess')->isActionAllowed() && count($this->getCustomer()->getAddresses()) < 2) {
            	$select->addOption('', Mage::helper('checkout')->__('New Address'));
            }

            return $select->getHtml();
        }
        return '';
    }
}