<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Config\Source;

class CalendarLanguage implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        $options = [];
        $options[] = ['value' => 'de', 'label' => __('German')];
        $options[] = ['value' => 'en', 'label' => __('English')];
        $options[] = ['value' => 'fr', 'label' => __('French')];
        $options[] = ['value' => 'it', 'label' => __('Italian')];
        $options[] = ['value' => 'ru', 'label' => __('Russian')];
        $options[] = ['value' => 'vi', 'label' => __('Vietnam')];
        
        return $options;
    }
}
