<?php

/*
 * Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block;

class View extends \Magento\Framework\View\Element\Template {

    protected $_event;
    protected $_coreRegistry = null;
    protected $_productFactory;
    protected $_eventsProductFactory;
    protected $_formKey;
    protected $_eventsHelper;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_date;
    protected $_customerSession;
    protected $_currentCustomer;
    
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
            \Magento\Framework\Registry $registry, 
            \Magento\Catalog\Model\ProductFactory $productFactory, 
            \Magebuzz\Events\Model\Catalog\ProductFactory $eventsProductFactory, 
            \Magento\Framework\Data\Form\FormKey $formKey,
            \Magebuzz\Events\Helper\Data $eventsHelper, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Framework\ObjectManagerInterface $objectManager, 
            \Magento\Framework\Stdlib\DateTime\DateTime $date, 
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_productFactory = $productFactory;
        $this->_eventsProductFactory = $eventsProductFactory;
        
        $this->_formKey = $formKey;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->_customerSession = $customerSession;
        $this->_currentCustomer = $currentCustomer;

        $this->_event = $this->getEvent();
    }

    public function getIdentities() {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'view'];
    }

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getEvent() {
        return $this->_coreRegistry->registry('current_event');
    }

    public function getAvatarUrl() {
        $avatarName = $this->_event->getAvatar();
        if ($avatarName != '') {
            $avatarUrl = $this->_eventsHelper->getImageUrl($avatarName, 'magebuzz/events/event/avatar/');
        } else {
            $defaultImage = $this->getScopeConfig('events/general_setting/default_image');
            $avatarUrl = $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/');
        }
        return $avatarUrl;
    }
    
    public function getPrice() {
        return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency(number_format($this->_event->getPrice(), 2), true, false);
    }

    public function getScopeConfig($path) {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getEventUrl() {
//            $url_path = 'event/' . $this->_event->getUrlKey() . '.html';
//            return $this->getUrl($url_path);
        
        return $this->getUrl('*/*/view', array('event_id' => $this->_event->getId()));
    }
    
    public function getFavoriteImageSrc() {
        if ($this->isFavorited()) {
            return $this->getViewFileUrl('Magebuzz_Events::images/heart-red.png');
        } else {
            return $this->getViewFileUrl('Magebuzz_Events::images/heart-white.png');
        }
    }
    
    public function isFavorited() {
        $customerId = $this->getCurrentCustomerId(); 
        if (empty($customerId)) {
            return false;
        }
        
        $favCustomerIds = $this->_event->getFavoritedCustomerIds();
        if(count($favCustomerIds) > 0 && in_array($customerId, $favCustomerIds)) {
            return true;
        }
        return false;
    }
    
        
    public function getCurrentCustomerId() {
        return $this->_coreRegistry->registry('current_customer_id');
//        return $this->_currentCustomer->getCustomerId(); 
//        return $this->_customerSession->isLoggedIn(); 
    }

    public function getRegisteredCount() {
        if ($this->_event->getPrice() > 0) {
            $productId = (int) $this->_event->getProductId();
            if ($productId && $productId > 0) {
                $product = $this->_eventsProductFactory->create()->load($productId);
                if ($product->getId()) {
                    return $product->getOrderedQty();
                }
            }
            return 0;
        }
        else {
            $participantIds = $this->_event->getParticipantIds();
            return count($participantIds);
        }
    }

    public function getRemainSlotCount() {
        $remainSlotCount = ( (int) $this->_event->getNumberOfParticipant() - (int) $this->getRegisteredCount());
        return $remainSlotCount;
    }

    public function isAssociatedProduct() {
        $isAssociatedProduct = false;
        $productId = (int) $this->_event->getProductId();
        if ($productId && $productId > 0) {
            $product = $this->_productFactory->create()->load($productId);
            if ($product->getId()) {
                $isAssociatedProduct = true;
            }
        } 
        return $isAssociatedProduct;
    }
    
    public function isAllowRegisterEvent() {
        $endTime = $this->_date->timestamp($this->_event->getEndTime());
        $registrationDeadline = $this->_date->timestamp($this->_event->getRegistrationDeadline());
        $currentTime = $this->_date->gmtTimestamp();

        $isStillNotDeadline = true;
        if ($registrationDeadline = $this->_event->getRegistrationDeadline()) {
            if ($this->_date->timestamp($registrationDeadline) < $currentTime) {
                $isStillNotDeadline = false;
            }
        }

        $allowRegister = false;
        if ($this->_event->getAllowRegister() == '1' && $endTime > $currentTime && $isStillNotDeadline && $this->getRemainSlotCount() > 0) {
            $allowRegister = true;
        }
        return $allowRegister;
    }

    
    public function getAddToCartUrl() {
        $productId = (int) $this->_event->getProduct();
        return $this->getUrl('events/index/addtocart', ['product' => $productId, 'formkey' => $this->_formKey->getFormKey()]);
        
//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $listBlock = $objectManager->get('\Magento\Catalog\Block\Product\ListProduct');
//        $product = $this->_productFactory->create()->load($productId);
//        return $listBlock->getAddToCartUrl($product, ['formkey'=> $this->_formKey->getFormKey()]);
    }
    
    public function getRegisterUrl() {
        return $this->getUrl('*/register/index', array('event_id' => $this->_event->getId()));
    }

    public function getStartTime() {
        $timestamp = $this->_date->timestamp($this->_event->getStartTime());
        return date('Y, M d g:i A', $timestamp);
    }

    public function getEndTime() {
        $timestamp = $this->_date->timestamp($this->_event->getEndTime());
        return date('Y, M d g:i A', $timestamp);
    }

    public function getRegistrationDeadline() {
        $timestamp = $this->_date->timestamp($this->_event->getRegistrationDeadline());
        return date('Y, M d g:i A', $timestamp);
    }

}
