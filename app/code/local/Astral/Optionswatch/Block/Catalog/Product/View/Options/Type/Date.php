<?php 


class Astral_Optionswatch_Block_Catalog_Product_View_Options_Type_Date extends Mage_Catalog_Block_Product_View_Options_Type_Date
{
    /**
     * Return drop-down html with range of values
     *
     * @param string $name Id/name of html select element
     * @param int $from  Start position
     * @param int $to    End position
     * @param int $value Value selected
     * @return string Formatted Html
     */
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {

    	$firstLabel = $name ? ucfirst($name) : '-';
    	$options = array(
    		array('value' => '', 'label' => $firstLabel)
    		);

    	for ($i = $from; $i <= $to; $i++) {
    		$options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros($i));
    	}

    	return $this->_getHtmlSelect($name, $value)
    	->setOptions($options)
    	->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = Mage::getSingleton('catalog/product_option_type_date')->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12,'');
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31,'');

        $yearStart = Mage::getSingleton('catalog/product_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/product_option_type_date')->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd,'');

        $translations = array(
            'd' => $daysHtml,
            'm' => $monthsHtml,
            'y' => $yearsHtml
        );
        return strtr($fieldsOrder, $translations);
    }

}

?>