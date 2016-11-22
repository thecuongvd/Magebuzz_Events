<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Register;

use Magento\Framework\App\Action\Action;

class Submitregistration extends Action
{
    protected $_eventFactory;
    protected $_participantFactory;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magebuzz\Events\Model\ParticipantFactory $participantFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_eventFactory = $eventFactory;
        $this->_participantFactory = $participantFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getPost();
        if ($post && !empty($post['event_id'])) {
            $eventId = $post['event_id'];
            $event = $this->_eventFactory->create()->load($eventId);

            //Check if allow to register
            $participantIds = $event->getParticipantIds();
            $remainSlotCount = ((int)$event->getRemainSlotCount());
            if ($remainSlotCount <= 0) {
                $this->messageManager->addError(__('This event was full of slot. So you can not register.'));
                return $resultRedirect->setPath('*/index/view', ['event_id' => $eventId]);
            }

            $participant = $this->_participantFactory->create();
            try {
                //Save info of participant
                $data = ['event_id' => $post['event_id'], 'fullname' => $post['fullname'], 'email' => $post['email'], 'phone' => $post['phone'], 'address' => $post['address']];
                $participant->setData($data)
                    ->setStatus(1)
                    ->save();

                //Send email
                if ($this->_scopeConfig->getValue('events/general_setting/is_send_registered_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) {
                    $event->sendRegisteredEmail($data);
                    $this->messageManager->addSuccess(__('Thank you for your registration. We sent an email to you about your registration.'));
                } else {
                    $this->messageManager->addSuccess(__('Thank you for your registration. We will contact you to confirm.'));
                }
                return $resultRedirect->setPath('*/index/view', ['event_id' => $eventId]);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('There was problem when submitting your request. Please try again.'));
                return $resultRedirect->setPath('*/*/index', ['event_id' => $eventId]);
            }
        }

    }

}
