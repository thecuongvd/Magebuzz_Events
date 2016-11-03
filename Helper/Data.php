<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $_jsonEncoder;

    /**
     * category collection factory.
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        CustomerSession $customerSession,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_backendUrl = $backendUrl;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_resourceHelper = $resourceHelper;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_fileSystem = $fileSystem;
        $this->_localeDate = $localeDate;
        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context);
    }

    /**
     * get image url.
     *
     * @return array
     */
    public function getImageUrl($imageName, $dir)
    {
        $path = $this->_fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath($dir);
        if(file_exists($path . $imageName)){
            $path = $this->_storeManager->getStore()->getBaseUrl( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $path . $dir . $imageName;
        }
        else{
            return '';
        }
    }
    
    public function isHeaderlinkEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            'events/general_setting/show_url_in_top_link',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }
    
    public function isToplinkEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            'events/general_setting/show_url_in_top_link',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }
    
}
