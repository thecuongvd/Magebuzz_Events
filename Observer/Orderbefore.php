<?php
namespace Excellence\Hello\Observer;
 
use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class Orderbefore implements ObserverInterface
{
    
    protected $resultRedirectFactory;
    protected $messageManager;
   
    public function __construct(
            Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
            Magento\Framework\Message\Manager $messageManager
            )
    {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }
 
    public function execute(Observer $observer)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addError(__('You must log in before add to favorite.'));
        return $resultRedirect->setPath('*/*/view', ['event_id' => $eventId, '_use_rewrite' => false]);
    }
}