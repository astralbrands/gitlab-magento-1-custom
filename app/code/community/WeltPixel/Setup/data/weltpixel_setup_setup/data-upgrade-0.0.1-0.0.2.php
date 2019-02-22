<?php

$identifier = 'weltpixel_footer_newsletter';

$staticBlock = Mage::getModel('cms/block')
    ->load($identifier, 'identifier');

if (null === $staticBlock->getId()) {
    $staticBlock = Mage::getModel('cms/block');
}

$staticBlock->setTitle('Footer Newsletter Static Block')
    ->setIdentifier($identifier)
    ->setIsActive(true)
    ->setStores(array(0))
    ->setContent(
        '<p>By subscribing to our mailing list you will<br />always be update with the latest news.</p>'
    )
    ->save();
