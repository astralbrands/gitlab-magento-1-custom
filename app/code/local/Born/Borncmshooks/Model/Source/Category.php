<?php
class Born_Borncmshooks_Model_Source_Category {

    public function toOptionArray() {

        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId,1,false,true);
        $categories = $this->getCategories($categories);

        return $categories;
    }


    public function getCategories($categories, &$array=array()) {

        foreach($categories as $category) {
            //$category = Mage::getModel('catalog/category')->load($category->getId());

            $array[] = array('label'=>$category->getName().' (ID: '.$category->getId().')', 'value'=>$category->getId());
            if($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $this->getCategories($children, $array);
            }
        }
        return  $array;
    }

}
?>