<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Wishlist;

use Magento\Framework\App\Action\Action;

class Delete extends Action
{
    protected $_eventFactory;
    protected $_eventsHelper;

    public function __construct(\Magento\Framework\App\Action\Context $context,
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
        $currentCustomerId = $this->_eventsHelper->getCustomerId();

        try {
            $this->_eventFactory->create()->load($eventId)->removeFavorite($currentCustomerId);
            $this->messageManager->addSuccess(__('You have deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was problem when delete item.'));
        } finally {
            return $resultRedirect->setPath('*/*/index');
        }
    }

}
