<?php
/*
 * Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block;

class View extends \Magento\Framework\View\Element\Template
{

    protected $_event;
    protected $_coreRegistry = null;
    protected $_eventsHelper;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_date;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_eventsHelper = $eventsHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_date = $date;

        $this->_event = $this->getEvent();
    }

    public function getEvent()
    {
        $event = $this->_coreRegistry->registry('events_event');
        $event->setProduct($event->getProduct());
        return $event;
    }

    public function getIdentities()
    {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'view'];
    }

    public function _prepareLayout()
    {
        $event = $this->getEvent();
        $this->_addBreadcrumbs($event);

        return parent::_prepareLayout();
    }

    protected function _addBreadcrumbs(\Magebuzz\Events\Model\Event $event)
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'events',
                [
                    'label' => __('Events'),
                    'title' => __('Go to Events Page'),
                    'link' => $this->getUrl('events')
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'event',
                [
                    'label' => $event->getTitle(),
                    'title' => $event->getTitle()
                ]
            );
        }
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPriceWithCurrency()
    {
        return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency(number_format($this->_event->getProduct()->getPrice(), 2), true, false);
    }

    public function getFavoriteImageSrc()
    {
        if ($this->isFavorited()) {
            return $this->getViewFileUrl('Magebuzz_Events::images/heart-red.png');
        } else {
            return $this->getViewFileUrl('Magebuzz_Events::images/heart-white.png');
        }
    }

    public function isFavorited()
    {
        $customerId = $this->getCustomerId();
        if (empty($customerId)) {
            return false;
        }

        $favCustomerIds = $this->_event->getFavoritedCustomerIds();
        if (count($favCustomerIds) > 0 && in_array($customerId, $favCustomerIds)) {
            return true;
        }
        return false;
    }

    public function getCustomerId()
    {
        return $this->_eventsHelper->getCustomerId();
    }

    public function getAvatarUrl()
    {
        $avatarUrl = $this->_event->getAvatarUrl();
        if ($avatarUrl == '') {
            $avatarUrl = $this->getViewFileUrl('Magebuzz_Events::images/default_event.jpg');
        }
        return $avatarUrl;
    }

    public function getFormattedTime($time)
    {
        $timestamp = $this->_date->timestamp($time);
        return date('M d, Y g:i A', $timestamp);
    }
    
    public function getFacebookButton() {
        $facebookID = '1082368948492595';
        $like_button = true;

        return '
            <div class="facebook_button social-button">
                <div id="fb-root"></div>
                <script>
                    (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=' . $facebookID . '";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, \'script\', \'facebook-jssdk\'));
                </script>
                <div class="fb-like" data-layout="button_count" data-width="400" data-show-faces="false"  data-href="' . $this->_event->getEventUrl() . '"  data-send="' . $like_button . '"></div>
            </div>';
    }

    public function getTwitterButton() {
        return "
            <div class='twitter_button social-button'>
                <a href='https://twitter.com/share' class='twitter-share-button' data-url='" . $this->_event->getEventUrl() . "' >Tweet</a>
                <script>
                    !function(d,s,id){
                        var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
                        if(!d.getElementById(id)){
                            js=d.createElement(s);
                            js.id=id;
                            js.src=p+'://platform.twitter.com/widgets.js';
                            fjs.parentNode.insertBefore(js,fjs);
                        }
                    }(document, 'script', 'twitter-wjs');
                </script>
            </div>";
    }

    public function getGooglePlusButton() {
        return '
            <div class="google_button social-button">
                <div class="g-plusone" data-size="medium"  data-annotation="bubble"></div>
            </div>
            <script src="https://apis.google.com/js/platform.js" async defer></script>';
    }

}
