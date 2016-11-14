<?php
/*
 * Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block;

class Register extends \Magento\Framework\View\Element\Template
{

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;

    }

    public function getIdentities()
    {
        return [\Magebuzz\Events\Model\Event::CACHE_TAG . '_' . 'view'];
    }

    public function getEvent()
    {
        return $this->_coreRegistry->registry('current_event');
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/submitregistration');
    }

}
