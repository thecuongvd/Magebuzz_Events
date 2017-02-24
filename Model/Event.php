<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model;

class Event extends \Magento\Framework\Model\AbstractModel
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const XML_PATH_INVITATION_EMAIL = 'events/general_setting/invitation_email';
    const XML_PATH_REGISTERED_EMAIL = 'events/general_setting/registered_email';
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'events_event';

    protected $_cacheTag = 'events_event';

    /**
     * Prefix of model name
     *
     * @var string
     */
    protected $_eventPrefix = 'events_event';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $_storeManager;
    protected $_scopeConfig;
    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $urlModel;
    protected $_productFactory;
    protected $_stockItem;
    protected $_eventsProductFactory;
    protected $_date;
    protected $_eventsHelper;
    protected $_formKey;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magebuzz\Events\Model\Catalog\ProductFactory $eventsProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->urlModel = $urlFactory->create();
        $this->_productFactory = $productFactory;
        $this->stockItem = $stockItem;
        $this->_eventsProductFactory = $eventsProductFactory;
        $this->_date = $date;
        $this->_eventsHelper = $eventsHelper;
        $this->_formKey = $formKey;
        $this->logger = $logger;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare statuses.
     * Available event events_event_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getStoreIds()
    {
        return $this->getResource()->getStoreIds($this->getId());
    }

    public function getCategoryIds()
    {
        return $this->getResource()->getCategoryIds($this->getId());
    }

    public function getEventAssociatedPrd($productId)
    {
        return $this->getResource()->getEventAssociatedPrd($productId);
    }

    public function sendInvitationEmail($senderName, $recipient, $message)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->inlineTranslation->suspend();

        try {
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->_scopeConfig->getValue(
                    self::XML_PATH_INVITATION_EMAIL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId))
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars(['event' => $this, 'message' => $message, 'recipient' => $recipient, 'senderName' => $senderName])
                ->setFrom(['email' => '', 'name' => $senderName])
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    public function sendRegisteredEmail($participantInfo)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->inlineTranslation->suspend();

        try {
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->_scopeConfig->getValue(
                    self::XML_PATH_REGISTERED_EMAIL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId))
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars(['event' => $this, 'participant' => $participantInfo])
                ->setFrom(['email' => '', 'name' => 'Registration'])
                ->addTo($participantInfo['email'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    public function getEventUrl()
    {
        return $this->urlModel->getUrl('events/index/view', ['event_id' => $this->getId()]);
    }

    public function getAvatarUrl()
    {
        $avatarName = $this->getAvatar();
        if ($avatarName != '') {
            $avatarUrl = $this->_eventsHelper->getImageUrl($avatarName, 'magebuzz/events/event/avatar/');
        } else {
            $defaultImage = $this->getScopeConfig('events/general_setting/default_image');
            if ($defaultImage && $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/')) {
                $avatarUrl = $this->_eventsHelper->getImageUrl($defaultImage, 'magebuzz/events/');
            } else {
                $avatarUrl = '';
            }
        }
        return $avatarUrl;
    }
    
    public function getAddToCartUrl()
    {
        $productId = (int)$this->getProductId();
        return $this->urlModel->getUrl('events/index/addtocart', ['product' => $productId, 'formkey' => $this->_formKey->getFormKey()]);
    }

    public function getRegisterUrl()
    {
        return $this->urlModel->getUrl('events/register/index', ['event_id' => $this->getId()]);
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFavoritedCustomerIds()
    {
        return $this->getResource()->getFavoritedCustomerIds($this->getId());
    }

    public function addFavorite($customerId)
    {
        $this->getResource()->addFavorite($this->getId(), $customerId);
    }

    public function removeFavorite($customerId)
    {
        $this->getResource()->removeFavorite($this->getId(), $customerId);
    }

    public function isAssociatedProduct()
    {
        $isAssociatedProduct = false;
        $productId = (int)$this->getProductId();
        if ($productId && $productId > 0) {
            $product = $this->_productFactory->create()->load($productId);
            if ($product->getId()) {
                $isAssociatedProduct = true;
            }
        }
        return $isAssociatedProduct;
    }

    public function isAllowRegisterEvent()
    {
        $endTime = $this->_date->timestamp($this->getEndTime());
        $registrationDeadline = $this->_date->timestamp($this->getRegistrationDeadline());
        $currentTime = $this->_date->gmtTimestamp();

        $isStillNotDeadline = true;
        if ($registrationDeadline = $this->getRegistrationDeadline()) {
            if ($this->_date->timestamp($registrationDeadline) < $currentTime) {
                $isStillNotDeadline = false;
            }
        }

        $allowRegister = false;
        if ($this->getAllowRegister() == '1' && $endTime > $currentTime && $isStillNotDeadline && $this->getRemainSlotCount() > 0) {
            $allowRegister = true;
        }
        return $allowRegister;
    }

    public function getRemainSlotCount()
    {
        $remainSlotCount = ((int)$this->getNoOfParticipant() - (int)$this->getRegisteredCount());
        return $remainSlotCount;
    }

    public function getNoOfParticipant()
    {
        if ($this->getProductId()) {
            return $this->getProductQty();
        } else {
            return $this->getNumberOfParticipant();
        }
    }

    public function getProductId()
    {
        return $this->getResource()->getProductId($this->getId());
    }

    public function getProductQty()
    {
        $product = $this->getProduct();
        if ($product && $productId = $product->getId()) {
            return $this->stockItem->getStockQty($productId, $product->getStore()->getWebsiteId());
        } else {
            return 0;
        }
    }

    public function getRegisteredCount()
    {
        if ($this->getPrice() > 0) {
            $productId = (int)$this->getProductId();
            if ($productId && $productId > 0) {
                $product = $this->_eventsProductFactory->create()->load($productId);
                if ($product->getId()) {
                    return $product->getOrderedQty();
                }
            }
            return 0;
        } else {
            $participantIds = $this->getParticipantIds();
            return count($participantIds);
        }
    }

    public function getPrice()
    {
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            return $product->getPrice();
        } else {
            return 0;
        }
    }

    public function getParticipantIds()
    {
        return $this->getResource()->getParticipantIds($this->getId());
    }

    protected function _construct()
    {
        $this->_init('Magebuzz\Events\Model\ResourceModel\Event');
    }
}
