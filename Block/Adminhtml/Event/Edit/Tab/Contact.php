<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Contact extends Generic implements TabInterface
{

    protected $_systemStore;
    protected $_groupRepository;
    protected $_searchCriteriaBuilder;
    protected $_objectConverter;
    protected $_eventsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Customer\Api\GroupRepositoryInterface $groupRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Framework\Convert\DataObject $objectConverter, \Magento\Store\Model\System\Store $systemStore, \Magebuzz\Events\Helper\Data $eventsHelper, array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_eventsHelper = $eventsHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Contact');
    }

    public function getTabTitle()
    {
        return __('Contact');
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
            'base_fieldset', ['legend' => __('Contact'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'is_show_contact', 'select', [
                'name' => 'is_show_contact',
                'label' => __('Show Contact Information'),
                'title' => __('Show Contact Information'),
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
                'onchange' => 'toggleContact(this.value)',
                'after_element_html' => $this->_isShowContactJs(),
            ]
        );
        $fieldset->addField(
            'contact_person', 'text', [
                'name' => 'contact_person',
                'label' => __('Contact Person'),
                'title' => __('Contact Person')
            ]
        );
        $fieldset->addField(
            'contact_phone', 'text', [
                'name' => 'contact_phone',
                'label' => __('Mobile Phone'),
                'title' => __('Mobile Phone')
            ]
        );
        $fieldset->addField(
            'contact_email', 'text', [
                'name' => 'contact_email',
                'label' => __('Email'),
                'title' => __('Email'),
                'class' => __('validate-email'),
            ]
        );
        $fieldset->addField(
            'contact_address', 'text', [
                'name' => 'contact_address',
                'label' => __('Address'),
                'title' => __('Address'),
                'after_element_html' => $event->getId() ? $this->_isShowContactAfterLoadJs() : '',
            ]
        );

        if (!$event->getId()) {
            $event->setData('is_show_contact', '1');
            $this->_isShowContactAfterLoadJs();
        }
        $form->setValues($event->getData());

        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _isShowContactJs()
    {
        return <<<HTML
    <script>
        function toggleContact(isShow) {
            if (isShow == '1') {
                document.getElementsByClassName('field-contact_person')[0].hidden = false;
                document.getElementsByClassName('field-contact_phone')[0].hidden = false;
                document.getElementsByClassName('field-contact_email')[0].hidden = false;
                document.getElementsByClassName('field-contact_address')[0].hidden = false;
            }
            else {
                document.getElementsByClassName('field-contact_person')[0].hidden = true;
                document.getElementsByClassName('field-contact_phone')[0].hidden = true;
                document.getElementsByClassName('field-contact_email')[0].hidden = true;
                document.getElementsByClassName('field-contact_address')[0].hidden = true;
            }
        }
    </script>
HTML;
    }

    protected function _isShowContactAfterLoadJs()
    {
        $event = $this->_coreRegistry->registry('events_event');
        $isShow = $event->getData('is_show_contact');
        return <<<HTML
    <script>
        toggleContact($isShow);
    </script>
HTML;
    }

}
