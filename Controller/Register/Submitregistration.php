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

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magebuzz\Events\Model\EventFactory $eventFactory,
                                \Magebuzz\Events\Model\ParticipantFactory $participantFactory
    )
    {
        $this->_eventFactory = $eventFactory;
        $this->_participantFactory = $participantFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $data = [];
        $data['event_id'] = $request->getPost('event_id');
        $event = $this->_eventFactory->create()->load($data['event_id']);
        $participantIds = $event->getParticipantIds();
        $remainSlotCount = ((int)$event->getNumberOfParticipant() - count($participantIds));
        if ($remainSlotCount <= 0) {
            $this->messageManager->addError(__('This event was full of slot. So you can not register.'));
            return $resultRedirect->setPath('*/index/view', ['event_id' => $data['event_id']]);
        }
        
        $data['fullname'] = $request->getPost('fullname');
        $data['email'] = $request->getPost('email');
        $data['phone'] = $request->getPost('phone');
        $data['address'] = $request->getPost('address');

        if ($data) {
            $participant = $this->_participantFactory->create();
            try {
                $participant->setData($data)
                    ->setStatus(1)
                    ->save();

                $this->messageManager->addSuccess(__('Thank you for your registration. We will contact you as soon as possible to confirm about this.'));
                return $resultRedirect->setPath('*/index/view', ['event_id' => $data['event_id']]);
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('There was problem when submitting your request. Please try again.'));
                return $resultRedirect->setPath('*/*/index', ['event_id' => $data['event_id']]);
            }
        }

    }

}
