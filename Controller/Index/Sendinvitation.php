<?php

namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class Sendinvitation extends Action {

    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $_eventFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry, \Magebuzz\Events\Model\EventFactory $eventFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_eventFactory = $eventFactory;
        parent::__construct($context);
    }

    public function execute() {
        if ($post = $this->getRequest()->getPost()) {
            $eventId = $post['event_id'];
            $event = $this->_eventFactory->create()->load($eventId);

            try {
                $event->sendInvitationEmail($post['yourname'], $post['friendemail'], $post['invitemessage']);

                $this->messageManager->addSuccess(__('You have sent your invitation to your friend'));

                $resultRedirect = $this->resultRedirectFactory->create();
            } catch (Exception $e) {
                $this->messageManager->addError(__('Unable to send you invitation. Please try again later'));
            } 
        }
        return $resultRedirect->setPath('*/*/view', ['event_id' => $event->getId()]);
    }

}
