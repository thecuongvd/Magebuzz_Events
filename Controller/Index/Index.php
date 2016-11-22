<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $viewMode = $this->getRequest()->getParam('mode');
        if ($viewMode) {
            $this->_coreRegistry->register('current_view_mode', $viewMode);
        }

        $catId = (int)$this->getRequest()->getPost('category', false);
        if ($catId) {
            $this->_coreRegistry->register('filter_cat_id', $catId);
        }
        $eventSearch = $this->getRequest()->getPost('event', false);
        if ($eventSearch) {
            $this->_coreRegistry->register('event_search', trim($eventSearch));
        }
        $locationSearch = $this->getRequest()->getPost('location', false);
        if ($locationSearch) {
            $this->_coreRegistry->register('location_search', trim($locationSearch));
        }

        $resultPage = $this->resultPageFactory->create();
        $pageTitle = $this->_scopeConfig->getValue('events/calendar_setting/page_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $resultPage->getConfig()->getTitle()->set($pageTitle);

        return $resultPage;

    }
}
