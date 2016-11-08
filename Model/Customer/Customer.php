<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
 
namespace Magebuzz\Events\Model\Customer;
  
class Customer extends \Magento\Customer\Model\Customer
{
    public function getFavoriteEventIds() {
        $select = $this->getConnection()->select()->from(
                        $this->getTable($this->_categoryEventTable), 'category_id')
                ->where('event_id = ?', $eventId);
        return $this->getConnection()->fetchCol($select);
    }
  
}