<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Gallery extends Generic implements TabInterface
{
    protected $_systemStore;

    protected $_eventsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_eventsHelper = $eventsHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Gallery');
    }

    public function getTabTitle()
    {
        return __('Gallery');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('events_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('event_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Gallery'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'avatar',
            'file',
            [
                'name'  => 'avatar',
                'label' => __('Event Avatar'),
                'title'  => __('Event Avatar'),
                'after_element_html' => $this->getImageHtml('avatar', $model->getAvatar(), 'magebuzz/events/event/avatar/')
            ]
        );
        
//        $fieldset->addField(
//            'gallery',
//            'file',
//            [
//                'name'  => 'gallery',
//                'label' => __('Images and Videos'),
//                'title'  => __('Images and Videos')
//            ]
//        );
   
        $form->setValues($model->getData());
        
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function getImageHtml($field, $imageName, $dir)
    {
        $html = '';
        if ($imageName) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<image style="min-width:100px;max-width:50%;" src="' . $this->_eventsHelper->getImageUrl($imageName, $dir) . '" />';
            $html .= '<input type="hidden" value="' . $imageName . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }
        return $html;
    }

}
