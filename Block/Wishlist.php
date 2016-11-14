<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Block;

/**
 * "My Ticket" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Wishlist extends \Magento\Framework\View\Element\Template
{
    protected $_eventFactory;
    protected $_storeManager;
    protected $_eventsHelper;
    protected $_scopeConfig;
    
    protected $_events;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_eventFactory = $eventFactory;
        $this->_storeManager = $storeManager;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
        
        $this->_events = $this->getEvents();
    }

    public function getPagedEvents() {
        return $this->_events;
    }

    protected function _prepareLayout() { 
        parent::_prepareLayout();
        if ($this->_events) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'events.event.index.pager')
                    ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50, 100 => 100])
                    ->setCollection($this->_events);
            $this->setChild('pager', $pager);
            $this->_events->load();
        }
        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    
    public function getEvents()
    {
        $customerId = $this->getCustomerId();
        if (empty($customerId)) {
            return null;
        }

        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_eventFactory->create()->getCollection()
            ->addFieldToFilter('status', 1)
            ->setOrder('start_time', 'ASC')
            ->setFavoriteFilter($customerId)
            ->setStoreFilter($storeIds);
        return $collection;
    }
    
    public function getCustomerId() {
        return $this->_eventsHelper->getCustomerId();
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    public function getAvatarUrl($event)
    {
        $avatarName = $event->getAvatar();
        if ($avatarName != '') {
            $avatarUrl = $this->_eventsHelper->getImageUrl($avatarName, 'magebuzz/events/event/avatar/');
        } else {
            $defaultImage = $this->getScopeConfig('events/general_setting/default_image');
            $avatarUrl = $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/');
        }
        return $avatarUrl;
    }
    
    public function getShortDescription($event) {
        $description = substr($event->getDescription(), 0, 100);
        if (strlen($event->getDescription()) > 100) {
            $description .= '.....';
        }
        return $description;
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
