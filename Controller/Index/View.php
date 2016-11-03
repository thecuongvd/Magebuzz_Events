<?php
namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class View extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_eventFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
            \Magento\Framework\Registry $registry,
            \Magebuzz\Events\Model\EventFactory $eventFactory
    ) { 
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_eventFactory = $eventFactory;
        parent::__construct($context); 
    }
    
    public function execute()
    {  
        $eventId = (int) $this->getRequest()->getParam('event_id', false);
        if (!$eventId) {
            return false;
        }
        $event = $this->_eventFactory->create()->load($eventId);
        
        if ($event->getId()) {
            $this->_coreRegistry->register('current_event', $event);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set($event->getTitle() . ' - ' . __('Events Calendar'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
        
    }

}
