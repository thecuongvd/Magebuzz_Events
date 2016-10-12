<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class UpcomingEventsPos implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'no', 'label' => __('No')], ['value' => 'leftcolumn', 'label' => __('Left Column')], ['value' => 'rightcolumn', 'label' => __('Right Column')]];
    }
}
