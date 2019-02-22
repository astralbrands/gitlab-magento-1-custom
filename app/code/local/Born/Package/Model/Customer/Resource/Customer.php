<?php

class Born_Package_Model_Customer_Resource_Customer extends Mage_Customer_Model_Resource_Customer {
    /**
     * Custom setter of increment ID if its needed
     *
     * @param Varien_Object $object
     * @return Mage_Customer_Model_Resource_Customer
     */
    public function setNewIncrementId(Varien_Object $object)
    {
        if (Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_GENERATE_HUMAN_FRIENDLY_ID)) {
            //var_dump($object->getData());die();

            if ($object->getIncrementId()) {
                return $this;
            }

            //creating customer from admin will use the sendmail store id otherwise system will always assign the admin store id to it.
            if (Mage::app()->getStore()->isAdmin())
                if($object->getStoreId()){
                    $incrementId = $this->getEntityType()->fetchNewIncrementId($object->getStoreId());
                }else{
                    $incrementId = $this->getEntityType()->fetchNewIncrementId($object->getSendemailStoreId());
                }
            else
                $incrementId = $this->getEntityType()->fetchNewIncrementId($object->getStoreId());

            if ($incrementId !== false) {
                $object->setIncrementId($incrementId);
            }

            //parent::setNewIncrementId($object);
        }
        return $this;
    }
}