<?php

/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct() {
        $this->_objectId = 'event_id';
        $this->_blockGroup = 'Magebuzz_Events';
        $this->_controller = 'adminhtml_event';

        parent::_construct();

        if ($this->_isAllowedAction('Magebuzz_Events::save')) {
            $this->buttonList->update('save', 'label', __('Save Event'));
            $this->buttonList->add(
                    'saveandcontinue', [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
                    ], -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Magebuzz_Events::event_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Event'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded blocklist
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText() {
        $model = $this->_coreRegistry->registry('events_event');
        if ($model->getId()) {
            return __("Edit Events '%1'", $this->escapeHtml($model->getTitle()));
        } else {
            return __('New Event');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl() {
        return $this->getUrl('events/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

}
