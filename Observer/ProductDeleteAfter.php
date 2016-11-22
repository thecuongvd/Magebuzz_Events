<?php
namespace Magebuzz\Events\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class ProductDeleteAfter implements ObserverInterface
{

    protected $_eventFactory;


    public function __construct(
        \Magebuzz\Events\Model\EventFactory $eventFactory
    )
    {
        $this->_eventFactory = $eventFactory;
    }

    public function execute(Observer $observer)
    {
        $productId = (int)$observer->getProduct()->getId();
        $event = $this->_eventFactory->create();
        $eventId = $event->getEventAssociatedPrd($productId);
        $event->load($eventId);
        $event->delete();
    }
}