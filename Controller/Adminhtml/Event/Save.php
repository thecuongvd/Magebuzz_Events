<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    protected $_fileSystem;
    protected $_fileUploaderFactory;
    protected $_logger;
    protected $jsHelper;
    protected $_date;
    protected $_eventFactory;
    protected $_productFactory;
    protected $_stockItem;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem
    )
    {
        parent::__construct($context);
        $this->_fileSystem = $fileSystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->jsHelper = $jsHelper;
        $this->_date = $date;
        $this->_eventFactory = $eventFactory;
        $this->_productFactory = $productFactory;
        $this->stockItem = $stockItem;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebuzz_Events::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $eventModel = $this->_objectManager->create('Magebuzz\Events\Model\Event');
            $id = $this->getRequest()->getParam('event_id');
            if ($id) {
                $eventModel->load($id);
                if ($id != $eventModel->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong event is specified.'));
                }
            }

            //Process time
            $localeDate = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            if ($data['start_time']) {
                $data['start_time'] = $localeDate->date($data['start_time'])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }
            if ($data['end_time']) {
                $data['end_time'] = $localeDate->date($data['end_time'])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }
            if ($data['registration_deadline']) {
                $data['registration_deadline'] = $localeDate->date($data['registration_deadline'])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }
            
            //Check if time is valid
            if ($data['start_time'] >= $data['end_time'] || $data['registration_deadline'] >= $data['end_time']) {
                if ($data['start_time'] >= $data['end_time']) {
                    $this->messageManager->addError( __('Start Time must be earlier than End Time.'));
                } 
                if ($data['registration_deadline'] && $data['registration_deadline'] >= $data['end_time']) {
                    $this->messageManager->addError( __('Registration Deadline must be earlier than End Time'));
                }
                $this->_getSession()->setFormData($data);
                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', ['event_id' => $id]);
                }
                else {
                    return $resultRedirect->setPath('*/*/new');
                }
                return $resultRedirect->setPath('*/*/', ['_current' => true]);
            }
            
            //Check if associated products
            if (!$id && !isset($data['product'])) {
                $this->messageManager->addError(__('You must associate product before save.'));
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/new');
            }
            
            //Check if Number of Participant is valid
            $productId = null;
            if (!empty($data['product'])) {
                $productId = $data['product'];
            } else if ($id) {
                $productId = $this->_eventFactory->create()->load($id)->getProductId();
            }
            $product = $this->_productFactory->create()->load($productId);
            if ($product->getId()) {
                $productQty = $this->stockItem->getStockQty($productId, $product->getStore()->getWebsiteId());
                if ($data['number_of_participant'] > $productQty) {
                    $this->messageManager->addError(__('Number of participant must not be larger than quantity of associated product ('.$productQty.').'));
                    $this->_getSession()->setFormData($data);
                    if ($id) {
                        return $resultRedirect->setPath('*/*/edit', ['event_id' => $id]);
                    }
                    else {
                        return $resultRedirect->setPath('*/*/new');
                    }
                }
            }

            //Process upload images
            $path = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath('magebuzz/events/event/avatar/');
            try {
                if (!empty($_FILES['avatar']['name'])) {
                    // remove the old file
                    $oldName = !empty($data['old_avatar']) ? $data['old_avatar'] : '';
                    if ($oldName) {
                        @unlink($path . $oldName);
                    }
                    //find the first available name
                    $newName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_FILES['avatar']['name']);
                    if (substr($newName, 0, 1) == '.') // all non-english symbols
                        $newName = 'event_' . $newName;
                    $i = 0;
                    while (file_exists($path . $newName)) {
                        $newName = ++$i . '_' . $newName;
                    }

                    /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'avatar']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->save($path, $newName);

                    $data['avatar'] = $newName;
                } else {
                    $oldName = !empty($data['old_avatar']) ? $data['old_avatar'] : '';
                    $data['avatar'] = $oldName;
                }
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->_logger->critical($e);
                }
            }

            //Process categories data
            if (isset($data['categories'])) {
                $data['categories'] = array_keys($this->jsHelper->decodeGridSerializedInput($data['categories']));
            }

            $eventModel->setData($data);

            $this->_eventManager->dispatch(
                'events_event_prepare_save', ['event' => $eventModel, 'request' => $this->getRequest()]
            );
            
            try {
                $eventModel->save();
                $this->messageManager->addSuccess(__('You saved this Event.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['event_id' => $id, '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the event.'));
            }

            $this->_getSession()->setFormData($data);
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['event_id' => $id]);
            } else {
                return $resultRedirect->setPath('*/*/new');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

}
