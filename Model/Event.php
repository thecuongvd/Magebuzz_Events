<?php

namespace Magebuzz\Events\Model;

class Event extends \Magento\Framework\Model\AbstractModel {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /*     * #@- */

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'events_event';

    protected $_cacheTag = 'events_event';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'events_event';

    function __construct(
    \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = []) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct() {
        $this->_init('Magebuzz\Events\Model\ResourceModel\Event');
    }

    /**
     * Prepare statuses.
     * Available event events_event_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

}
