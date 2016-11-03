<?php
namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
            \Magento\Framework\Registry $registry,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) { 
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    
    public function execute()
    {  
        $catId = (int) $this->getRequest()->getParam('c', false);
        $viewMode = $this->getRequest()->getParam('mode');
        if ($catId) {
            $this->_coreRegistry->register('current_event_cat_id', $catId);
        }
        if ($viewMode) {
            $this->_coreRegistry->register('current_view_mode', $viewMode);
        }
        
        $resultPage = $this->resultPageFactory->create();
        $pageTitle = $this->_scopeConfig->getValue('events/calendar_setting/page_title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $resultPage->getConfig()->getTitle()->set($pageTitle);

        return $resultPage;
        
    }
}
