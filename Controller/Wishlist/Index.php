<?php
namespace Magebuzz\Events\Controller\Wishlist;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_customerSession;

    public function __construct(\Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
            \Magento\Framework\Registry $registry,
            \Magento\Customer\Model\Session $customerSession
    ) { 
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function execute()
    {  
        $currentCustomerId = $this->_customerSession->getCustomerId();
        $this->_coreRegistry->register('current_customer_id', $currentCustomerId);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Events')); 
        return $resultPage;
    }

}
