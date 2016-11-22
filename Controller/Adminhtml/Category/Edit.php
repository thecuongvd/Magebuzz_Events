<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{

    protected $_coreRegistry = null;
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry
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
        $id = $this->getRequest()->getParam('category_id');
        $model = $this->_objectManager->create('Magebuzz\Events\Model\Category');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This category no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('events_category', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Event Category') : __('New Event Category'), $id ? __('Edit Event Category') : __('New Event Category')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Event Category'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Edit Category ') . $model->getCategoryTitle() : __('New Category'));

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
        $resultPage->setActiveMenu('Magebuzz_Events::manage_categories')
            ->addBreadcrumb(__('Event Categories'), __('Event Categories'))
            ->addBreadcrumb(__('Manage Event Categories'), __('Manage Event Categories'));
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
