<?php

namespace Magebuzz\Events\Block;

class Events extends \Magento\Framework\View\Element\Template  {
    
    protected $_coreRegistry = null;
    protected $_eventFactory;
    protected $_categoryFactory;
    protected $_eventsHelper;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_date;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
            \Magebuzz\Events\Model\EventFactory $eventFactory, 
            \Magebuzz\Events\Model\CategoryFactory $categoryFactory, 
            \Magento\Framework\Registry $registry,
            \Magebuzz\Events\Helper\Data $eventsHelper,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Stdlib\DateTime\DateTime $date, 
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_eventFactory = $eventFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_coreRegistry = $registry;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
    }

    public function getIdentities() {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'list'];
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','events.pager');
//        $pager->setLimit(10);
        $pager->setAvailableLimit(array(10 => 10, 20 => 20, 50 => 50, 100 => 100));
        $pager->setCollection($this->_getEventCollection());
        $this->setChild('pager', $pager);// set pager block in layout
        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    protected function _getEventCollection() {
        $storeIds = [0, $this->getCurrentStoreId()];
        
        $collection = $this->_eventFactory->create()->getCollection()
                ->addFieldToFilter('status', 1)
                ->setOrder('start_time', 'ASC')
                ->setStoreFilter($storeIds);

        $catId = $this->getFilterCatId();
        $eventSearch = $this->getEventSearch();
        $locationSearch = $this->getLocationSearch();
        if ($catId) {
            $collection->setCatFilter($catId);
        }
        if ($eventSearch) {
            $collection->setEventNameFilter($eventSearch);
        }
        if ($locationSearch) {
            $collection->setLocationFilter($locationSearch);
        }
        
        return $collection;
    }
    
    /*
     * get a list of events in JSON
     */
    public function getEventJson() {
        $defaultColor = '#3366CC';
        $collection = $this->_getEventCollection();
        $results = array();

        if (count($collection)) {
            foreach ($collection as $event) {
                $item = array(
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'url' => $this->getEventUrl($event),
                    'avatar_url' => $this->getAvatarUrl($event),
                    'reg_deadline' => $event->getRegistrationDeadline(),
                    'location' => $event->getLocation(),
                    'description' => $event->getDescription(),
                ); 

                $item['start'] = date('Y-m-d H:i:s', $this->_date->timestamp($event->getStartTime()) + $this->_date->getGmtOffset());
                $item['end'] = date('Y-m-d H:i:s', $this->_date->timestamp($event->getEndTime()) + $this->_date->getGmtOffset());
                $item['allDay'] = false;
                if ($event->getColor() != '') {
                    $item['color'] = '#' . $event->getColor();
                } else {
                    $item['color'] = $defaultColor;
                }
                $results[] = $item;
            }
        }

        if (!count($results)) {
            return false;
        }
        return json_encode($results);
    }
    
    public function getEventUrl($event) {
//        $url_path = 'event/' . $event->getUrlKey() . '.html';
//        return $this->getUrl() . $url_path;
        
        return $this->getUrl('*/*/view', array('event_id' => $event->getId()));
    }
    
    public function getAvatarUrl($event) {
        $avatarName = $event->getAvatar();
        if ($avatarName != '') {
            $avatarUrl = $this->_eventsHelper->getImageUrl($avatarName, 'magebuzz/events/event/avatar/');
        } else {
            $defaultImage = $this->getScopeConfig('events/general_setting/default_image');
            $avatarUrl = $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/');
        }
        return $avatarUrl;
    }
    
    public function getScopeConfig($path) {
        return $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getFormattedTime($time) {
        $timestamp = $this->_date->timestamp($time);
        return date('Y, M d g:i A', $timestamp);
    }
    
    public function getCurrentStoreId() {
        return $this->_storeManager->getStore(true)->getId();
        
    }
    
    public function getFilterCatId() {
        return $this->_coreRegistry->registry('filter_cat_id');
    }
    public function getEventSearch() {
        return $this->_coreRegistry->registry('event_search');
    }
    public function getLocationSearch() {
        return $this->_coreRegistry->registry('location_search');
    }
    
    public function getCurrentMode() {
        $mode = $this->_coreRegistry->registry('current_view_mode');
        $availableMode = $this->getAvailableMode();
        $modes = array_keys($availableMode);
        $defaultMode = current($modes);
        
        if (!$mode || !isset($availableMode[$mode])) {
            $mode = $defaultMode;
        }

        return $mode;
    }
    
    public function getEventCategories() {
        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_categoryFactory->create()->getCollection()
                ->addFieldToFilter('status', 1)
                ->setStoreFilter($storeIds);
        return $collection;
    }
    
    public function getAvailableMode() {
        switch ($this->getScopeConfig('events/general_setting/view_mode')) {
            case 'calendar':
                $availableMode = array('calendar' => 'Calendar');
                break;

            case 'list':
                $availableMode = array('list' => 'List');
                break;

            case 'calendar-list':
                $availableMode = array('calendar' => 'Calendar', 'list' => 'List');
                break;

            case 'list-calendar':
                $availableMode = array('list' => 'List', 'calendar' => 'Calendar');
                break;
        }
        return $availableMode;
    }

}
