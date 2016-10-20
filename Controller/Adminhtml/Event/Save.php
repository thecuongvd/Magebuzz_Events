<?php

namespace Magebuzz\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action {
    protected $_fileSystem;

    protected $_fileUploaderFactory;

    protected $_logger;
    
    protected $jsHelper;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\Helper\Js $jsHelper
    ) {
        parent::__construct($context);
        $this->_fileSystem = $fileSystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->jsHelper = $jsHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Magebuzz_Events::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Magebuzz\Events\Model\Event');
            $id = $this->getRequest()->getParam('event_id');
            if ($id) {
                $model->load($id);
                if ($id != $model->getId()) {
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

            //Process upload images
            $path = $this->_fileSystem->getDirectoryRead(
                            DirectoryList::MEDIA
                    )->getAbsolutePath(
                    'magebuzz/events/event/avatar/'
            );
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
            if(isset($data['categories'])){
                $data['categories'] = array_keys($this->jsHelper->decodeGridSerializedInput($data['categories']));
            }
            
            //Process products data
            if (isset($data['products'])) {
                $data['products'] = array_keys($this->jsHelper->decodeGridSerializedInput($data['products']));
            }
            
//            echo '<pre>';
//            print_r($data); 
//            echo '</pre>';
//            die();
            $model->setData($data);
            
            $this->_eventManager->dispatch(
                    'events_event_prepare_save', ['event' => $model, 'request' => $this->getRequest()]
            );
            try {
                $model->save(); 
                $this->messageManager->addSuccess(__('You saved this Event.')); 
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false); 
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['event_id' => $model->getId(), '_current' => true]);
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
                return $resultRedirect->setPath('*/*/edit', ['event_id' => $this->getRequest()->getParam('event_id')]);
            }
            else {
                return $resultRedirect->setPath('*/*/new');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

}
