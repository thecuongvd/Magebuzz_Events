<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class Favorite extends Action
{
    protected $_eventFactory;
    protected $_eventsHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magebuzz\Events\Helper\Data $eventsHelper
    )
    {
        $this->_eventFactory = $eventFactory;
        $this->_eventsHelper = $eventsHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $eventId = $this->getRequest()->getParam('event_id');
        $action = $this->getRequest()->getParam('action');
        $currentCustomerId = $this->_eventsHelper->getCustomerId();

        if (!$currentCustomerId) {
            $this->messageManager->addError(__('You must sign in to save this event to "My Events" in your account dashboard.'));
            return $resultRedirect->setPath('*/*/view', ['event_id' => $eventId]);
        }

        try {
            if ($action == 'add') {
                $this->_eventFactory->create()->load($eventId)->addFavorite($currentCustomerId);
                $this->messageManager->addSuccess(__('This event has been added successfully to "My Events" in your account dashboard.'));
            } else if ($action == 'remove') {
                $this->_eventFactory->create()->load($eventId)->removeFavorite($currentCustomerId);
                $this->messageManager->addSuccess(__('This event have been removed from favorite successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was problem when ' . ($action == 'add') ? 'add to favorite' : 'remove from favorite' . '. Please try again.'));
        } finally {
            return $resultRedirect->setPath('*/*/view', ['event_id' => $eventId]);
        }

    }

}
