<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\Category\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $_model;

    public function __construct(
        \Magebuzz\Events\Model\Category $model
    )
    {
        $this->_model = $model;
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_model->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

}
