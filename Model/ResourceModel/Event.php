<?php
namespace Magebuzz\Events\Model\ResourceModel;
 
use Magento\Framework\Model\AbstractModel;
 
/**
* Mysql resource
*/
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
  /**
  * Block entity table
  *
  * @var string
  */
  protected $_blockEventTable;
 
  public function __construct(
      \Magento\Framework\Model\ResourceModel\Db\Context $context,
      $resourcePrefix = null
      ) {
          parent::__construct($context, $resourcePrefix);
      }
 
  /**
  * Initialize resource model
  *
  * @return void
  */
  protected function _construct()
  {
      $this->_init('mb_events', 'event_id');
  }
 
}