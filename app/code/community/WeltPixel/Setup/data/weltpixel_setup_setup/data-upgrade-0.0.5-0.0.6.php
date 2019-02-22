<?php

$identifier = 'weltpixel_global_promo_message';

$staticBlock = Mage::getModel('cms/block')
    ->load($identifier, 'identifier');

if (null === $staticBlock->getId()) {
    $staticBlock = Mage::getModel('cms/block');
}

$staticBlock->setTitle('Global Promo Message')
    ->setIdentifier($identifier)
    ->setIsActive(true)
    ->setStores(array(0))
    ->setContent(
        '<span>Global Promo Message Content. Edit me in weltpixel_global_promo_message static block.</span>'
    )
    ->save();
