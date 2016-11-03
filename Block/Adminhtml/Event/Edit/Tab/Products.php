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
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_coreRegistry = $coreRegistry;
        
        $this->_linkFactory = $linkFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context, $backendHelper, $data);
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
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_linkFactory->create()->getProductCollection()
            ->addAttributeToSelect(
                '*'
            )
            ->addFieldToFilter('type_id', 'event' )
            ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
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

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productgrid',['_current' => true]);
    }

    /**
     * Retrieve selected items key
     *
     * @return array
     */
    protected function _getSelectedProduct()
    {
        $id = $this->getRequest()->getParam('event_id', 0);

        $event = $this->_eventFactory->create()->load($id);
        $product =  [$event->getProduct()];
        return $product;
    }

}