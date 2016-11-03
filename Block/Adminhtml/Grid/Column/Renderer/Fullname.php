<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Grid\Column\Renderer;

class Fullname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $fullname = $row->getFirstname() . ' ' . $row->getMiddlename() . ' ' . $row->getLastname();
        return $fullname;
    }
}