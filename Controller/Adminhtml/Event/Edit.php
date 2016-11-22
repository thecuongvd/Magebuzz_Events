<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Adminhtml\Event;

class Edit extends \Magento\Backend\App\Action
{

    protected $_coreRegistry = null;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Edit Action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('event_id');
        $event = $this->_objectManager->create('Magebuzz\Events\Model\Event');

        if ($id) {
            $event->load($id);
            if (!$event->getId()) {
                $this->messageManager->addError(__('This event no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $event->setData($data);
        }

        $this->_coreRegistry->register('events_event', $event);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Events') : __('New Events'), $id ? __('Edit Events') : __('New Events')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));
        $resultPage->getConfig()->getTitle()
            ->prepend($event->getId() ? __('Edit Event ') . $event->getTitle() : __('New Event'));

        return $resultPage;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebuzz_Events::manage_events')
            ->addBreadcrumb(__('Events'), __('Events'))
            ->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebuzz_Events::save');
    }

}
