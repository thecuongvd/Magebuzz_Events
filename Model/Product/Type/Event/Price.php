<?php

namespace Magebuzz\Events\Model\Product\Type\Event;
 class Price extends \Magento\Catalog\Model\Product\Type\Price
 {
     public function getPrice($product) {
         return $product->getData('price');
     }
     
 }
