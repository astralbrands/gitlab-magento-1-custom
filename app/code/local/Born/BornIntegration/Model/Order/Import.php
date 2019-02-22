<?php 

class Born_BornIntegration_Model_Order_Import extends Born_BornIntegration_Model_Integration {

    protected function _getCarriers($object)
    {
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
            $object->getStoreId()
        );

        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }

        return $carriers;
    }

    public function importShipping() {
        if($this->helper->isEnabled()) {
            $remote = $this->connect();

            #TODO: cd to folder with product update file
            #TODO: create processed folder if doesn't exist
            
            foreach($shipments as $shipment) {
                #orderIncrementId, itemsQty (array), comment, email, includecomment, carrier, title, trackNumber
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

                if (!$order->getId()) {
                     $this->helper->error('order_not_exists: ' . $orderIncrementId);
                }

                if (!$order->canShip()) {
                     $this->helper->error('Cannot do shipment for order: ' . $orderIncrementId);
                }

                $shipment = $order->prepareShipment($itemsQty);
                if ($shipment) {
                    $shipment->register();
                    $shipment->addComment($comment, $email && $includeComment);
                    if ($email) {
                        $shipment->setEmailSent(true);
                    }
                    $shipment->getOrder()->setIsInProcess(true);
                    try {
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($shipment)
                            ->addObject($shipment->getOrder())
                            ->save();
                        $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                    } catch (Mage_Core_Exception $e) {
                        $this->helper->error($e->getMessage());
                    }
                    
                    $carriers = $this->_getCarriers($shipment);

                    if (!isset($carriers[$carrier])) {
                        $this->helper->error('Invalid carrier specified: ' . $shipment->getIncrementId());
                    }

                    $track = Mage::getModel('sales/order_shipment_track')
                                ->setNumber($trackNumber)
                                ->setCarrierCode($carrier)
                                ->setTitle($title);

                    $shipment->addTrack($track);

                    try {
                        $shipment->save();
                        $track->save();
                    } catch (Mage_Core_Exception $e) {
                        $this->_fault('data_invalid', $e->getMessage());
                    }
                }
            }

            #TODO: close connection: $this->closeConnection();
        }
    }

}