<?php

namespace Magebuzz\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magebuzz\Events\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassChangeStatus extends \Magento\Backend\App\Action {

    protected $filter;
    protected $collectionFactory;

    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute() {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $item) {
            $status = $item->getStatus();
            $status = !$status;
            $item->setStatus($status);
            $item->save();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been changed status.', $collection->getSize()));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

}
