<?php

namespace Magebuzz\Events\Block\Adminhtml;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container {

    protected function _construct() {
        $this->_controller = 'adminhtml_event';
        $this->_blockGroup = 'Magebuzz_Events';
        $this->_headerText = __('Manage Events');

        parent::_construct();

        if ($this->_isAllowedAction('Magebuzz_Events::save')) {
            $this->buttonList->update('add', 'label', __('Add New Event'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

}
