<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class ViewMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'grid', 'label' => __('Grid')], ['value' => 'calendar', 'label' => __('Calendar')], ['value' => 'grid-calendar', 'label' => __('Grid(default)/Calendar')], ['value' => 'calendar-grid', 'label' => __('Calendar(default)/Grid')]];
    }
}
