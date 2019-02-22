<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/9/13
 * Time   : 4:28 PM
 * File   : Expiry.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Block_Adminhtml_Widget_Grid_Column_Render_Expiry
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function _getValue(Varien_Object $row)
    {
        $format = ( $this->getColumn()->getFormat() ) ? $this->getColumn()->getFormat() : null;
        $defaultValue = $this->getColumn()->getDefault();

        // If no format and it column not filtered specified return data as is.
        $data = parent::_getValue($row);
        $string = $data===null ? $defaultValue : $data;

        $string = $this->helper('ebizmarts_sagepaymentspro')->getCardNiceDate($string);

        return htmlspecialchars($string);
    }
}