<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Block;

class Sidebar extends \Magento\Framework\View\Element\Template
{

    protected $_eventFactory;
    protected $_scopeConfig;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_eventFactory = $eventFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function getIdentities()
    {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'sidebar'];
    }

    public function isShowUpcomingEvents()
    {
        $isShowUpcomingEvents = $this->getScopeConfig('events/general_setting/show_upcoming_events');
        if ($isShowUpcomingEvents && $isShowUpcomingEvents == '1') {
            return true;
        } else {
            return false;
        }
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUpcomingEventCollection()
    {
        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_eventFactory->create()->getCollection()
            ->addFieldToFilter('status', 1)
            ->setUpcomingFilter()
            ->setOrder('start_time', 'ASC')
            ->setStoreFilter($storeIds);
        return $collection;
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();

    }

    public function getEventUrl($event)
    {
//        $url_path = 'event/' . $event->getUrlKey() . '.html';
//        return $this->getUrl() . $url_path;

        return $this->getUrl('events/index/view', array('event_id' => $event->getId()));
    }
}
