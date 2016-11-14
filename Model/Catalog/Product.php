<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Catalog;

class Product extends \Magento\Catalog\Model\Product
{
    public function getOrderedQty()
    {
        $qty = 0;
        $orderItemCollection = $this->getOrderItemCollection();
        foreach ($orderItemCollection as $item) {
            $itemQty = $item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled();
            $qty += $itemQty;
        }
        return $qty;
    }

    public function getOrderItemCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderItemCollection = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Item\Collection')
            ->addFieldToFilter('product_id', $this->getId());
        return $orderItemCollection;
    }

    public function getOrdererAddressCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderItemCollection = $this->getOrderItemCollection();
        $orderIds = [];
        foreach ($orderItemCollection as $item) {
            $orderIds[] = $item->getOrderId();
        }
        $orderIds = array_unique($orderIds);

        $ordererAddressCollection = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Address\Collection')
            ->addAttributeToFilter('address_type', 'billing')
            ->addAttributeToFilter('parent_id', ['in' => $orderIds]);
        return $ordererAddressCollection;
    }

}