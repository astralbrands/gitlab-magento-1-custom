<?php 

class Born_Package_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        $displayHandlingFee = $this->getDisplayHandlingFee();

        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }

            $totalHtml = $this->renderTotal($total, $area, $colspan);

            if (!$displayHandlingFee) {
            	if ($total->getCode() != 'handling' ) {
            		$html .= $totalHtml;
            	}
            }else{
            	$html .= $totalHtml;
            }
        }
        return $html;
    }

    protected function getDisplayHandlingFee()
    {
    	$path = 'checkout/cart/display_handling_fee';
    	$config = Mage::getStoreConfig($path);

    	if ($config) {
    		return true;
    	}

    	return false;
    }
}

 ?>