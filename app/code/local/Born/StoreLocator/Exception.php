<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Exception extends Mage_Core_Exception {
      
	 protected $_context;
	  
	 function getContext() {
	     return $this->_context;
	 }
	  
	 function __construct($message = "" , $context=null, $code = 0, $previous = NULL) {
	     parent::__construct($message,$code,$previous);
	     $this->_context=$context;
	 }
}   