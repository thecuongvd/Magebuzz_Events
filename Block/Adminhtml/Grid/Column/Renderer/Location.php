<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Grid\Column\Renderer;

class Location extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $fullname = $row->getData('street') . ' - ' . $row->getCity();
        return $fullname;
    }
}