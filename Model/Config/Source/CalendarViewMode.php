<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class CalendarViewMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'day', 'label' => __('Day')], ['value' => 'week', 'label' => __('Week')], ['value' => 'month', 'label' => __('Month')]];
    }
}
