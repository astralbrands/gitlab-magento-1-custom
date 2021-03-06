<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_System_Config_Form_Field_Findandreplace
    extends RocketWeb_ShoppingFeeds_Block_Adminhtml_System_Config_Form_Field_Mapcolumns
{

    public function __construct($attributes=array())
    {

        $this->addColumn(
            'find', array(
                'label' => Mage::helper('adminhtml')->__('Find'),
                'style' => 'width:200px',
            )
        );

        $this->addColumn(
            'replace', array(
                'label' => Mage::helper('adminhtml')->__('Replace'),
                'style' => 'width:200px',
            )
        );

        $this->addColumn(
            'columns', array(
                'label' => Mage::helper('adminhtml')->__('Map Columns'),
                'style' => 'width:100px',
                'class' => 'validate-greater-than-zero',
            )
        );


        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Rule');

        parent::__construct();

        $this->setData($attributes);

        if(!$this->getName() && $this->getFieldName()) {
            $this->setName($this->getFieldName());
        }
        if(!$this->getElement()) {
            $this->setElement($this);
        }
        $this->setTemplate('rocketshoppingfeeds/system/config/form/field/array.phtml');
    }

    public function configToJson()
    {
        return Zend_Json::encode(array('attributes' => array()));
    }

    public function allowFillParams($column)
    {
        return false;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param  string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }

        $html = '';
        if ($columnName == 'columns') {
            $html .= '<select name="' . $inputName . '" ' . (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '>';
            foreach ($this->getMapColumns() as $option) {
                $html .= '<option ' . (isset($option['style']) ? 'style="' . $option['style'] . '" ' : '') . 'label="' . $option['label'] . '" value="' . $option['value'] . '">' . $option['label'] . '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
                ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
                (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
        }

        return $html;
    }
}