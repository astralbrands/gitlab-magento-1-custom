<?php
/**
 * @author Astral Brands Team
 * @package Astral_Integrations
 */


class Astral_Integrations_Helper_OrderDecorator extends Mage_Core_Helper_Abstract {

    private function getRevenue($order, $excludeShipping = false) {
        $revenue = (float) $order->getGrandTotal();

        $revenue -= (float) $order->getTaxAmount();

        if ($excludeShipping) {
            $revenue -= (float) $order->getShippingAmount();
        }

        return $revenue;

    }

    public function getCriteoOrderEventArray($order) {
        
        $criteoOrder = array();
        if (isset($order) && !empty($order)) {

            $criteoOrder['event'] = 'trackTransaction';
            $criteoOrder['id'] = $order->getIncrementId();
            $criteoOrder['item'] = array();
            $orderItems = $order->getAllVisibleItems();

            foreach($orderItems as $item) {
                $row = array();
                $row['id'] = $item->getSku();
                $row['price'] = number_format($item->getPrice(), 2);
                $row['quantity'] = number_format($item->getData('qty_ordered'),0);
                $criteoOrder['item'][] = $row;
            }

        }

        return $criteoOrder;
    }

    public function getMavrckOrderEvent($order, $mavrickId = '') {
        $appliedRules = array();

        if (isset($order) && !empty($order)) {

            if (strlen($order->getAppliedRuleIds()) > 0) {
                $couponCode;
                $rules = explode(",",$order->getAppliedRuleIds());

                foreach($rules as $ruleId){
                    $rule = Mage::getModel('salesrule/rule')->load($ruleId);
                    $couponCode = $rule->getCouponCode(); 
                    if (isset($couponCode) && !empty($couponCode) && $couponCode != 'null') {
                        array_push($appliedRules, $rule->getCouponCode());
                    }
                }
            }

            $couponCodes = json_encode($appliedRules);
            $total = number_format($this->getRevenue($order, true), 2);

            return 'mvk("fireConversion", "' . $mavrickId . '", "' . $total . '","", "' . $order->getIncrementId() . '", "' . $couponCodes . '");';

        }

        return 'console.log("invalid order")';
    }

    public function getCommissionJunctionOrderEvent($order, $cJid, $merchantType, $containerId) {
        $event = array();
        if (isset($order) && !empty($order)) {

            $event['OID'] = $order->getIncrementId();
            $event['CID'] = $cJid;
            $event['containerTagId'] = $containerId;
            $event['TYPE'] = $merchantType;
            $event['total'] = number_format($this->getRevenue($order), 2); 
            $event['discount'] = number_format($order->getDiscountAmount(), 2);
            $event['currency'] = $order->getOrderCurrencyCode();
            $event['couponCode'] = $order->getCouponCode();
            $orderItems = $order->getAllVisibleItems();
            $i = 1;

            foreach($orderItems as $item) {
                $amtKey = 'AMT' . $i;
                $itemKey = 'ITEM' . $i;
                $qtyKey = 'QTY' . $i;

                $event[$itemKey] = $item->getSku();
                $event[$amtKey] = number_format($item->getPrice(), 2);
                $event[$qtyKey] = number_format($item->getData('qty_ordered'), 0);

                $i++;
            }
        }

        return $event;
    }

    public function getPixelOrderEvent($order) {
        $pixelOrderEvent = array();

        if (isset($order) && !empty($order)) {

            $pixelOrderEvent['currency'] = $order->getOrderCurrencyCode();
            $pixelOrderEvent['value'] = number_format($this->getRevenue($order), 2); 
            $pixelOrderEvent['content_type'] = 'product';
            $pixelOrderEvent['contents'] = array();
            $pixelOrderEvent['content_ids'] = array();
            $orderItems = $order->getAllVisibleItems();
            
            foreach ($order->getAllVisibleItems() as $item) {
                $row = array();
                $row['id'] = $item->getSku();
                $row['item_price'] = number_format($item->getPrice(), 2);
                $row['quantity'] = number_format($item->getData('qty_ordered'), 0);

                $pixelOrderEvent['content_ids'][] = $item->getSku();
                $pixelOrderEvent['contents'][] = $row;
            }
        }

        return $pixelOrderEvent;
    }

}
