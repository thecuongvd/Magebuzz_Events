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
class Wishlist extends \Magento\Framework\View\Element\Html\Link
{
    protected $_coreRegistry = null;
    protected $_eventFactory;
    protected $_storeManager;
    protected $_eventsHelper;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magebuzz\Events\Model\EventFactory $eventFactory, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_eventFactory = $eventFactory;
        $this->_storeManager = $storeManager;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getFavoriteEventCollection() {
        $currentCustomerId = $this->_coreRegistry->registry('current_customer_id');
        if (empty($currentCustomerId)) {
            return null;
        }
        
        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_eventFactory->create()->getCollection()
                ->addFieldToFilter('status', 1)
                ->setOrder('start_time', 'ASC')
                ->setFavoriteFilter($currentCustomerId)
                ->setStoreFilter($storeIds);
        return $collection;
    }
    
    public function getCurrentStoreId() {
        return $this->_storeManager->getStore(true)->getId();
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
}
