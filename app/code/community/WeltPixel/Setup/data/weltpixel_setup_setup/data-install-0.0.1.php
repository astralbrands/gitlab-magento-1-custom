<?php

$identifier = 'weltpixel_empty_cart';

$staticBlock = Mage::getModel('cms/block')
    ->load($identifier, 'identifier');

if (null === $staticBlock->getId()) {
    $staticBlock = Mage::getModel('cms/block');
}

$staticBlock->setTitle('Empty Cart Page Static Block')
    ->setIdentifier($identifier)
    ->setIsActive(true)
    ->setStores(array(0))
    ->setContent(
        '<p>This block can be edited from the static static block with the following identifier: ' . $identifier . '</p>'
    )
    ->save();
