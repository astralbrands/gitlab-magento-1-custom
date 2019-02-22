<?php

class WeltPixel_ShadeGuide_Block_Foundationfinder extends Mage_Core_Block_Template
{
    private $_helper;

    /**
     * WeltPixel_ShadeGuide_Block_Foundationfinder constructor.
     */
    public function __construct()
    {
        parent::_construct();

        $this->_helper = Mage::helper('shadeguide');
    }

    /**
     * @return mixed
     */
    public function getFinderSteps()
    {
        return unserialize($this->_helper->getConfigValue('step_builder', 'steps'));
    }

    /**
     * @return mixed
     */
    public function getFirstFinderStep()
    {
        $steps = $this->getFinderSteps();
        return reset($steps);
    }

    /**
     * @param $step
     * @return bool
     */
    public function isFirstStep($step)
    {
        $firstStep = $this->getFirstFinderStep();
        if (count(array_diff($firstStep, $step))) {
            return false;
        }

        return true;
    }

    /**
     * @param $step
     * @return array
     */
    public function getStepOptions($step)
    {
        $options = array();
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $step['product_attribute']);

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        return $options;
    }

    /**
     * @param $optionId
     * @return bool
     */
    public function getOptionImage($optionId)
    {
        $optionObj = Mage::getModel('eav/entity_attribute_option')->load($optionId);
        if ($optionObj->getProductImage() != '') {
            return $optionObj->getProductImage();
        }

        return false;
    }

    public function getAjaxUrl($action)
    {
        return $this->getUrl(
            'shadeguide/foundationfinder/' . $action,
            array('_secure' => true)
        );
    }

    public function setCurrentStep($stepId, $step)
    {
        $this->deleteCurrentStep();

        Mage::register('current_step_id', $stepId);
        Mage::register('current_step', $step);

    }

    public function getCurrentStepId()
    {
        return Mage::registry('current_step_id');
    }

    public function getCurrentStep()
    {
        return Mage::registry('current_step');
    }

    public function deleteCurrentStep()
    {
        Mage::unregister('current_step_id');
        Mage::unregister('current_step');
    }
}