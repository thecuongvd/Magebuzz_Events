<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Block\Adminhtml;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'Magebuzz_Events';
        $this->_headerText = __('Manage Events Categories');

        parent::_construct();

        if ($this->_isAllowedAction('Magebuzz_Events::save')) {
            $this->buttonList->update('add', 'label', __('Add New Category'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
