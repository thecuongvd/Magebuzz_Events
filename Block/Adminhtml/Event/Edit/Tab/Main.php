<?php

/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{

    protected $_systemStore;
    protected $_eventsHelper;
    protected $defaultColor = '3366CC';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_eventsHelper = $eventsHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Event Information');
    }

    public function getTabTitle()
    {
        return __('Event Information');
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
        $event = $this->_coreRegistry->registry('events_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('event_');

        $fieldset = $form->addFieldset(
            'base_fieldset', ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($event->getId()) {
            $fieldset->addField('event_id', 'hidden', ['name' => 'event_id']);
        }

        $fieldset->addField(
            'title', 'text', ['name' => 'title', 'label' => __('Event Name'), 'title' => __('Event Name'), 'required' => true]
        );
        $fieldset->addField(
            'location', 'text', ['name' => 'location', 'label' => __('Location'), 'title' => __('Location')]
        );
        $fieldset->addField(
            'price', 'text', ['name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'class' => __('validate-zero-or-greater')]
        );
        $fieldset->addField(
            'number_of_participant', 'text', ['name' => 'number_of_participant',
                'label' => __('Number of Participant'),
                'title' => __('Number of Participant'),
                'required' => true,
                'class' => __('validate-zero-or-greater')]
        );
        $fieldset->addField(
            'avatar',
            'file',
            [
                'name' => 'avatar',
                'label' => __('Event Avatar'),
                'title' => __('Event Avatar'),
                'after_element_html' => $this->getImageHtml('avatar', $event->getAvatar(), 'magebuzz/events/event/avatar/')
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
        $style = 'color: #000;background-color: #fff; font-weight: bold; font-size: 13px;';

        $fieldset->addField(
            'start_time', 'date', [
                'name' => 'start_time',
                'label' => __('Start Time'),
                'title' => __('Start Time'),
                'style' => $style,
                'required' => true,
                'class' => __('validate-date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT),
            ]
        );
        $fieldset->addField(
            'end_time', 'date', [
                'name' => 'end_time',
                'label' => __('End Time'),
                'title' => __('End Time'),
                'style' => $style,
                'required' => true,
                'class' => __('validate-date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT),
            ]
        );
        $fieldset->addField(
            'registration_deadline', 'date', [
                'name' => 'registration_deadline',
                'label' => __('Registration Deadline'),
                'title' => __('Registration Deadline'),
                'style' => $style,
                'class' => __('validate-date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT),
            ]
        );
        $fieldset->addField(
            'description', 'textarea', ['name' => 'description',
                'label' => __('Description'),
                'title' => __('Description')]
        );

        if (!$this->_storeManager->hasSingleStore()) { //Check is single store mode
            $field = $fieldset->addField(
                'select_stores', 'multiselect', [
                    'label' => __('Store View'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $event->setSelectStores($event->getStores());
        } else {
            $fieldset->addField(
                'select_stores', 'hidden', [
                    'name' => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $event->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'allow_register', 'select', [
                'name' => 'allow_register',
                'label' => __('Allow Register'),
                'title' => __('Allow Register'),
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        $fieldset->addField(
            'status', 'select', ['name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        $fieldset->addField(
            'color', 'text', ['name' => 'color',
                'label' => __('Color'),
                'title' => __('Color'),
                'class' => __('color')]
        );

        if (!$event->getId()) {                         //Add new
            $event->setData('status', '1');
            $event->setData('allow_register', '1');
        }
        if (empty($event->getData('color'))) {
            $event->setData('color', $this->defaultColor);
        }
        $form->setValues($event->getData());
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
