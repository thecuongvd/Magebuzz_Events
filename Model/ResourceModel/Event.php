<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Mysql resource
 */
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $_eventStoreTable;
    protected $_categoryEventTable;
    protected $_participantTable;
    protected $_productEventTable;
    protected $_productFactory;
    protected $_favoriteTable;
    protected $_customerTable;
    protected $_date;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->_productFactory = $productFactory;
    }

    public function getParticipantIds($eventId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_participantTable), 'participant_id')
            ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchCol($select);
    }

    public function getFavoritedCustomerIds($eventId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_favoriteTable), 'customer_id')
            ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchCol($select);
    }

    public function addFavorite($eventId, $customerId)
    {
        $connection = $this->getConnection();
        $favInsert = ['event_id' => $eventId, 'customer_id' => $customerId];
        $connection->insert($this->_favoriteTable, $favInsert);
    }

    public function removeFavorite($eventId, $customerId)
    {
        $connection = $this->getConnection();
        $favCondition = ['event_id=?' => $eventId, 'customer_id=?' => $customerId];
        $connection->delete($this->_favoriteTable, $favCondition);
    }

    protected function _construct()
    {
        $this->_init('mb_events', 'event_id');
        $this->_eventStoreTable = $this->getTable('mb_event_store');
        $this->_categoryEventTable = $this->getTable('mb_event_category');
        $this->_participantTable = $this->getTable('mb_participants');
        $this->_productEventTable = $this->getTable('mb_event_product');
        $this->_favoriteTable = $this->getTable('mb_event_favorite');
        $this->_customerTable = $this->getTable('customer_entity');
    }

    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        if (!$object->getId()) {   //if create new
            return $this;
        }

        //Process time
        if ($object->hasStartTime()) {
            $startTime = date('Y-m-d H:i:s', $this->_date->timestamp($object->getStartTime()) + $this->_date->getGmtOffset());
            $object->setStartTime($startTime);
        }
        if ($object->hasEndTime()) {
            $endTime = date('Y-m-d H:i:s', $this->_date->timestamp($object->getEndTime()) + $this->_date->getGmtOffset());
            $object->setEndTime($endTime);
        }
        if ($object->hasRegistrationDeadline()) {
            $registrationDeadline = date('Y-m-d H:i:s', $this->_date->timestamp($object->getRegistrationDeadline()) + $this->_date->getGmtOffset());
            $object->setRegistrationDeadline($registrationDeadline);
        }

        // load event available in stores
        $object->setStores($this->getStoreIds((int)$object->getId()));

        // load categories associate to this event
        $object->setCategories($this->getCategoryIds((int)$object->getId()));

        // load product associate to this event
        $object->setProduct($this->getProductId((int)$object->getId()));

        return $this;
    }

    public function getStoreIds($eventId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_eventStoreTable), 'store_id')
            ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchCol($select);
    }

    public function getCategoryIds($eventId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_categoryEventTable), 'category_id')
            ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchCol($select);
    }

    public function getProductId($eventId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_productEventTable), 'entity_id')
            ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchOne($select);
    }

    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedTime()) {
            $object->setCreatedTime($this->_date->gmtDate());
        }

        if ($object->hasData('stores') && !is_array($object->getStores())) {
            $object->setStores([$object->getStores()]);
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();

        //Save event_stores
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = ['event_id = ?' => $object->getId()];
            $connection->delete($this->_eventStoreTable, $condition);

            $insertedStoreIds = [];
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = ['store_id' => $storeId, 'event_id' => $object->getId()];
                $connection->insert($this->_eventStoreTable, $storeInsert);
            }
        }

        //save event_categories
        $categories = $object->getCategories();
        if (!empty($categories)) {
            $condition = ['event_id = ?' => $object->getId()];
            $connection->delete($this->_categoryEventTable, $condition);

            $insertedCategoryIds = [];
            foreach ($categories as $categoryId) {
                if (in_array($categoryId, $insertedCategoryIds)) {
                    continue;
                }

                $insertedCategoryIds[] = $categoryId;
                $categoryInsert = ['category_id' => $categoryId, 'event_id' => $object->getId()];
                $connection->insert($this->_categoryEventTable, $categoryInsert);
            }
        }

        //save event_product
        $productId = $object->getProduct();
        if (!empty($productId)) {
            $condition = ['event_id = ?' => $object->getId()];
            $connection->delete($this->_productEventTable, $condition);

            $productInsert = ['entity_id' => $productId, 'event_id' => $object->getId()];
            $connection->insert($this->_productEventTable, $productInsert);
        }

        //save quantity of associated product equal number of participants
//        $this->_productFactory->create()->load($this->getProductId($object->getId()))->setQty($object->getNumberOfParticipant());

        return $this;
    }

}
