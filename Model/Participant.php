<?php

namespace Magebuzz\Events\Model;

class Participant extends \Magento\Framework\Model\AbstractModel {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /*     * #@- */

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'events_participant';

    protected $_cacheTag = 'events_participant';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'events_participant';

    function __construct(
    \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = []) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct() {
        $this->_init('Magebuzz\Events\Model\ResourceModel\Participant');
    }

    public function getAvailableStatuses() {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

}
