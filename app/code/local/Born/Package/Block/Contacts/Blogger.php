<?php

/**
 * Astal Brand
 *
 * @category    Astal Brand
 * @package     Born_Package
 * @date        23/01/2016
 * @author      Tanuja
 * @description submiting the form to the controller blogger having action submit.
 */
class Born_Package_Block_Contacts_Blogger extends Mage_Core_Block_Template
{

    public function getSubmitAction()
    {
        return Mage::getUrl('professional_form/blogger/submit');
    }
}

?>
