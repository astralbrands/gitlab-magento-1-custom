<?php

class Turnto_Admin_IndexController extends Mage_Core_Controller_Front_Action
{
    public function versionAction() {
        echo Mage::getConfig()->getNode()->modules->Turnto_Admin->version;
    }
}


