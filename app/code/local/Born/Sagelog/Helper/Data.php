<?php

class Born_Sagelog_Helper_Data extends Mage_Core_Helper_Abstract {

    public function sendTransactionalEmail($id = NULL) {
        if ($id == NULL) {
            return false;
        }
        $errorLog = Mage::getModel('sagelog/logging')->load($id);
        // Transactional Email Template's ID
        $templateId = Mage::getStoreConfig('sage_log_config/general/email_price_template');

        // Set sender information            
        $senderName = Mage::getStoreConfig('sage_log_config/general/from_name');
        $senderEmail = Mage::getStoreConfig('sage_log_config/general/from_email');
        $sender = array('name' => $senderName,
            'email' => $senderEmail);

        // Set recepient information
        $recepientEmail = explode(',',Mage::getStoreConfig('sage_log_config/general/to_email'));
        $recepientName = Mage::getStoreConfig('sage_log_config/general/to_name');

        // Get Store ID        
        $storeId = Mage::app()->getStore()->getId();

        // Set variables that can be used in email template
        $vars = array('error_type' => $errorLog->getErrorType(),
            'error_message' => $errorLog->getErrorMessage(),
            'order_id' => $errorLog->getOrderId(),
            'customer_id' => $errorLog->getCustomerId()
        );

        $translate = Mage::getSingleton('core/translate');
        // Send Transactional Email
        Mage::getModel('core/email_template')
                ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);

        $translate->setTranslateInline(true);
    }

    public function saveErrorLog($error_type, $error_message, $order_id, $customer_id, $sendemailflag = false) {
        $errorLog = Mage::getModel('sagelog/logging');
        $errorLog->setErrorType($error_type)
                ->setErrorMessage($error_message)
                ->setOrderId($order_id)
                ->setCustomerId($customer_id)
                ->save();
        if($sendemailflag) {
            $this->sendTransactionalEmail($errorLog->getId());
        }
        return $errorLog->getId();
    }

}
