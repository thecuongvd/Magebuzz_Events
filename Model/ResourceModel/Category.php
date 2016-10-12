<?php
namespace Magebuzz\Events\Model\ResourceModel;
 
use Magento\Framework\Model\AbstractModel;
 
/**
* Events category mysql resource
*/
class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
  /**
  * Block category entity table
  *
  * @var string
  */
  protected $_blockCategoryTable;
 
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
      $this->_init('mb_categories', 'category_id');
  }
 
}