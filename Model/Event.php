<?php

namespace Magebuzz\Events\Model;

class Event extends \Magento\Framework\Model\AbstractModel {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const XML_PATH_INVITATION_EMAIL = 'events/general_setting/invitation_email';
    /* 
     * CMS page cache tag
     */
    const CACHE_TAG = 'events_event';

    protected $_cacheTag = 'events_event';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'events_event';
    
    protected $_storeManager;
    protected $scopeConfig;
    protected $_transportBuilder;
    protected $inlineTranslation;
     protected $urlModel;

    function __construct(
    \Magento\Framework\Model\Context $context, 
            \Magento\Framework\Registry $registry, 
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
            \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
            \Magento\Framework\UrlFactory $urlFactory,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, 
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, 
            array $data = []) 
    {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct() {
        $this->_init('Magebuzz\Events\Model\ResourceModel\Event');
    }

    /**
     * Prepare statuses.
     * Available event events_event_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getStoreIds() {
        return $this->getResource()->getStoreIds($this->getId());
    }
    
    public function getCategoryIds() {
        return $this->getResource()->getCategoryIds($this->getId());
    }
    
    public function getParticipantIds() {
        return $this->getResource()->getParticipantIds($this->getId());
    }
    
    public function getProductId() {
        return $this->getResource()->getProductId($this->getId());
    }
    
    public function sendInvitationEmail($senderName, $recipient, $message)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->inlineTranslation->suspend();

        try {
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(
                    self::XML_PATH_INVITATION_EMAIL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId))
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars(['event' => $this, 'message' => $message, 'recipient' => $recipient, 'senderName'=>$senderName])
                ->setFrom(['email' => '', 'name' => $senderName])
                ->addTo($recipient)
//                ->setReplyTo('', $senderName)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        }
        catch (Exception $e) {
            //silence is gold
        }
    }
    
    public function getEventUrl() {
        return $this->urlModel->getUrl('*/*/view', ['event_id' => $this->getId()]);
    }
    
    public function getFavoritedCustomerIds() {
        return $this->getResource()->getFavoritedCustomerIds($this->getId());
    }
    
    public function addFavorite($customerId) {
        $this->getResource()->addFavorite($this->getId(), $customerId);
    }
    public function removeFavorite($customerId) {
        $this->getResource()->removeFavorite($this->getId(), $customerId);
    }
}
