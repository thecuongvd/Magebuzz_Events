<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_eventFactory;
    protected $_linkFactory;
    protected $_productStatus;
    protected $_productVisibility;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_eventFactory = $eventFactory;
        $this->_linkFactory = $linkFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productgrid', ['_current' => true]);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('events_products_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getEvent() && $this->getEvent()->getId()) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * Retirve currently edited model
     *
     * @return \Magebuzz\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->_coreRegistry->registry('events_event');
    }
    
    
    /**
     * Retrieve selected items key
     *
     * @return array
     */
    protected function _getSelectedProduct()
    {
        $eventId = $this->getRequest()->getParam('event_id', 0);

        $event = $this->_eventFactory->create()->load($eventId);
        $product = [$event->getProduct()];
        return $product;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_linkFactory->create()->getProductCollection();
        $eventId = $this->getRequest()->getParam('event_id', 0);
        if ($eventId) {
            $event = $this->_eventFactory->create()->load($eventId);
            if ($event->getId()) {
                $productId = $event->getProduct();
                $collection->addFieldToFilter('entity_id', $productId);
            } 
        } else {
            $associatedProductIds = [];
            $events = $this->_eventFactory->create()->getCollection();
            foreach ($events as $event) {
                $associatedProductIds[] = $event->getProductId();
            }
            
            //Get id of products that have type 'event' and haven't associated with any event
            $eventProductIds = $this->getEventProductIds();
            foreach ($eventProductIds as $key=>$id) {
                if (in_array($id, $associatedProductIds)) {
                    unset($eventProductIds[$key]);
                }
            }
                    
            $collection->addFieldToFilter('type_id', 'event')
                ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()])
                ->addFieldToFilter('entity_id', ['in' => $eventProductIds]);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    public function getEventProductIds() {
        $eventProductIds = [];
        $productCollection = $this->_linkFactory->create()->getProductCollection()
                ->addFieldToFilter('type_id', 'event')
                ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
        foreach ($productCollection as $product) {
            $eventProductIds[] = $product->getId();
        }
        return $eventProductIds;
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $event = $this->_coreRegistry->registry('events_event');
        $this->addColumn(
            'in_product',
            [
                'type' => 'radio',
                'html_name' => 'product',
                'values' => $this->_getSelectedProduct(),
                'align' => 'center',
                'index' => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'product_thumbnail',
            [
                'header' => __('Thumbnail'),
                'align' => 'left',
                'width' => '97',
                'renderer' => 'Magebuzz\Events\Block\Adminhtml\Grid\Column\Renderer\Thumbnail'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'product_price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        return parent::_prepareColumns();
    }
}