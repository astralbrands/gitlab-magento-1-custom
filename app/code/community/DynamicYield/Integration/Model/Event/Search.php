<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Event_Search
 */
class DynamicYield_Integration_Model_Event_Search extends DynamicYield_Integration_Model_Event_Abstract
{
    protected $searchQuery;

    /**
     * @return mixed
     */
    function getName() {
        return 'Keyword Search';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'keyword-search-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('keywords' => NULL);
    }

    function generateProperties() {
        return array('keywords' => $this->searchQuery);
    }

    /**
     * @param mixed $searchQuery
     */
    public function setSearchQuery($searchQuery) {
        $this->searchQuery = $searchQuery;
    }
}
