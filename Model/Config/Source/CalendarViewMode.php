<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class CalendarViewMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'agendaDay', 'label' => __('Day')], ['value' => 'agendaWeek', 'label' => __('Week')], ['value' => 'month', 'label' => __('Month')]];
    }
}
