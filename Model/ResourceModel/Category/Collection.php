<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\ResourceModel\Category;

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
            ['store_table' => $this->getTable('mb_event_category_store')],
            'main_table.category_id = store_table.category_id',
            []
        );
        $inCondition = $connection->prepareSqlCondition('store_table.store_id', ['in' => $storeIds]);
        $this->getSelect()->where($inCondition)
            ->group(array('main_table.category_id'));
        return $this;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebuzz\Events\Model\Category', 'Magebuzz\Events\Model\ResourceModel\Category');
        $this->_idFieldName = 'category_id';
    }

}
