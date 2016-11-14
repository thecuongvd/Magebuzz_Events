<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_fileSystem;
    protected $_customerSession;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_storeManager = $storeManager;
        $this->_fileSystem = $fileSystem;
        $this->_customerSession = $customerSession;

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
        if (file_exists($path . $imageName)) {
            $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $path . $dir . $imageName;
        } else {
            return '';
        }
    }

    public function isHeaderlinkEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'events/general_setting/show_url_in_header_link',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getCustomerId() {
        if($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomerId();
        }  
        return null;
    }

}
