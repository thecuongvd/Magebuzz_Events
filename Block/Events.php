<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Block;

class Events extends \Magento\Framework\View\Element\Template {

    protected $_coreRegistry = null;
    protected $_eventFactory;
    protected $_categoryFactory;
    protected $_eventsHelper;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_date;
    protected $_events;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magebuzz\Events\Model\EventFactory $eventFactory, \Magebuzz\Events\Model\CategoryFactory $categoryFactory, \Magento\Framework\Registry $registry, \Magebuzz\Events\Helper\Data $eventsHelper, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Stdlib\DateTime\DateTime $date, array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_eventFactory = $eventFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_coreRegistry = $registry;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;

        $this->_events = $this->getEvents();
    }

    public function getIdentities() {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'list'];
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

    public function getEvents() {
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

    public function getEventJson() {
        $defaultColor = '#3366CC';
        $collection = $this->getEvents();
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
                    'description' => $this->getShortDescription($event),
                );

                $item['start'] = date('Y-m-d H:i:s', $this->convertTimeToLocal($event->getStartTime()));
                $item['end'] = date('Y-m-d H:i:s', $this->convertTimeToLocal($event->getEndTime()));
                $item['allDay'] = false;
                if ($this->getScopeConfig('events/calendar_setting/show_event_color') && $event->getColor() != '') {
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

        return $this->getUrl('events/index/view', array('event_id' => $event->getId()));
    }

    public function getAvatarUrl($event) {
        $avatarName = $event->getAvatar();
        if ($avatarName != '') {
            $avatarUrl = $this->_eventsHelper->getImageUrl($avatarName, 'magebuzz/events/event/avatar/');
        } else {
            $defaultImage = $this->getScopeConfig('events/general_setting/default_image');
            if ($defaultImage && $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/')) {
                $avatarUrl = $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/');
            } else {
                $avatarUrl = $this->getViewFileUrl('Magebuzz_Events::images/default_event.jpg');
            }
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

    public function getScopeConfig($path) {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function convertTimeToLocal($time) {
        return $this->_date->timestamp($time) + $this->_date->getGmtOffset();
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
            case 'grid':
                $availableMode = array('grid' => 'Grid');
                break;
            case 'calendar-grid':
                $availableMode = array('calendar' => 'Calendar', 'grid' => 'Grid');
                break;
            case 'grid-calendar':
                $availableMode = array('grid' => 'Grid', 'calendar' => 'Calendar');
                break;
            default:
                $availableMode = array('calendar' => 'Calendar', 'grid' => 'Grid');
                break;
        }
        return $availableMode;
    }

}
