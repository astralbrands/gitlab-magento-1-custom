<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */
class Amasty_Ogrid_Model_Observer 
{
    protected static $_grid;

    public function onSalesOrderItemSaveAfter($observer)
    {
        
        $orderItem = $observer->getEvent()->getItem();
        
        $amOrderItem = Mage::getModel("amogrid/order_item");
                
        $amOrderItem->mapOrder($orderItem);
        
        return true;  
    } 
    
    public function modifyOrderCollection($observer)
    {

        $collection = $observer->getCollection();


        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection
                || $collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection
                ) {
            
            $permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
            
            $noneSalesOrder = !(strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') !== false ||
                strpos(Mage::app()->getRequest()->getControllerName(), 'adminhtml_order') !== false);
            if ($noneSalesOrder  ||
                 !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) ) {

                return;
            }

            $layout = Mage::getSingleton('core/layout');
            $grid = $layout->getBlock('sales_order.grid');

            if (!$grid) {
                foreach ($layout->getAllBlocks() as $block) {
//                    var_dump(get_class($block));
                    if ($block instanceof  Mage_Adminhtml_Block_Sales_Order_Grid ||
                        $block instanceof AW_Marketsuite_Block_Adminhtml_Order_Grid) {
                        $grid = $block;
                        break;
                    }
                }
            }

            if ($grid) {

                /** @var Amasty_Ogrid_Helper_Columns $columns */
                $columns = Mage::helper("amogrid/columns");

                $requireAttributeJoin = false;

                /** @var Mage_Adminhtml_Block_Widget_Grid_Column $column */
                foreach ($grid->getColumns() as $column) {
                    if ($column->getData('amogrid_require_join')) {
                        $dir = $column->getDir();
                        $filterValue = $column->getFilter()->getValue();
                        if ($dir || $filterValue) {
                            $requireAttributeJoin = true;
                            break;
                        }
                    }
                }

                $columns->prepareOrderCollectionJoins($collection, array(), $requireAttributeJoin);
                
                $columns->removeColumns($grid);
                
                $columns->reorder($grid);
                
                $columns->restyle($grid);
                
            }
        }
    }
    
    
    
    public function modifyOrderGridAfterBlockGenerate($observer)
    {
        $permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
        $exportActions = array('exportCsv', 'exportExcel');
//        var_dump(Mage::app()->getRequest()->getControllerName(), Mage::app()->getRequest()->getControllerName());
//        exit;
        if ( !(
                Mage::app()->getRequest()->getControllerName() == 'adminhtml_order' ||
                Mage::app()->getRequest()->getControllerName() == 'sales_order' ||
                Mage::app()->getRequest()->getControllerName() == 'adminhtml_sales_order') ||
                
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) ) {
             
            return;
        }
        
        $export = in_array(Mage::app()->getRequest()->getActionName(), $exportActions);
        
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid || $block instanceof AW_Marketsuite_Block_Adminhtml_Order_Grid) {

            self::$_grid = $block;
            $columns = Mage::helper("amogrid/columns");
            $columns->prepareGrid($block, $export);

            $showMessage = Mage::helper('amogrid')->checkNewFields();
            if ($showMessage && !Mage::app()->getRequest()->getParam('ajax')) {
                $urlAmogridSettings = Mage::helper("adminhtml")->getUrl("adminhtml/amogrid_settings");
                $link = '<a href=' . $urlAmogridSettings . ' target="_blank">Order Grid Columns</a>';
                $message = 'You can add new fields to this grid on the following page: ' . $link;
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('amogrid')->__($message));
            }
        }
        
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Column && $export) {
            if (self::$_grid) {
                $columns = Mage::helper("amogrid/columns");
                $columns->removeColumns(self::$_grid);
            }
        }
    }

}
