<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class ViewMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'list', 'label' => __('List')], ['value' => 'calendar', 'label' => __('Calendar')], ['value' => 'list-calendar', 'label' => __('List(default)/Calendar')], ['value' => 'calendar-list', 'label' => __('Calendar(default)/List')]];
    }
}
