<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Product\Type\Event;
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    public function getPrice($product)
    {
        return $product->getData('price');
    }

}
