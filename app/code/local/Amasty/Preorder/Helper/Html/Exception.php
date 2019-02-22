<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


/**
 * Created by PhpStorm.
 * User: yuri
 * Date: 01/09/15
 * Time: 13:31
 */
class Amasty_Preorder_Helper_Html_Exception extends Mage_Exception
{
    function __construct($pattern) {
        $code = preg_last_error();
        $message = sprintf('Preorder: Error while matching pattern "%s". Preg error code: %d.', $pattern, $code);
        parent::__construct($message);
    }
}