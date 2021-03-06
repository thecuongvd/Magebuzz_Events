<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\ResourceModel\Event;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Set store filter
     *
     * @param int $storeIds
     * @return $this
     */
    public function setStoreFilter($storeIds)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $connection = $this->getConnection();
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds === null ? -1 : $storeIds];
        }
        if (empty($storeIds)) {
            return $this;
        }
        $this->getSelect()->distinct(true)->join(
            ['store_table' => $this->getTable('mb_event_store')],
            'main_table.event_id = store_table.event_id',
            []
        );
        $inCondition = $connection->prepareSqlCondition('store_table.store_id', ['in' => $storeIds]);
        $this->getSelect()->where($inCondition);
        return $this;
    }

    public function setCatFilter($catId)
    {
        $connection = $this->getConnection();

        if (empty($catId)) {
            return $this;
        }
        $this->getSelect()->distinct(true)->join(
            ['cat_table' => $this->getTable('mb_event_category')],
            'main_table.event_id = cat_table.event_id',
            []
        );
        $inCondition = $connection->prepareSqlCondition('cat_table.category_id', $catId);
        $this->getSelect()->where($inCondition)
            ->group(array('main_table.event_id'));
        return $this;
    }

    public function setEventNameFilter($eventSearch)
    {
        $this->getSelect()->where("title LIKE '%$eventSearch%'");
        return $this;
    }

    public function setLocationFilter($locationSearch)
    {
        $this->getSelect()->where("location LIKE '%$locationSearch%'");
        return $this;
    }

    public function setUpcomingFilter()
    {
        $this->getSelect()->where("TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(),main_table.start_time) > 0");
        return $this;
    }

    public function setFavoriteFilter($customerId)
    {
        $connection = $this->getConnection();
        $this->getSelect()->distinct(true)->join(
            ['fav_table' => $this->getTable('mb_event_favorite')],
            'main_table.event_id = fav_table.event_id',
            []
        );
        $inCondition = $connection->prepareSqlCondition('fav_table.customer_id', $customerId);
        $this->getSelect()->where($inCondition)
            ->group(array('main_table.event_id'));
        return $this;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebuzz\Events\Model\Event', 'Magebuzz\Events\Model\ResourceModel\Event');
        $this->_idFieldName = 'event_id';
    }

}
