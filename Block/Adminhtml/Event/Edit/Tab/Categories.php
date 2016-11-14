<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

class Categories extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_eventFactory;

    protected $_categoryFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magebuzz\Events\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        $this->_eventFactory = $eventFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/categorygrid', ['_current' => true]);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('events_category_grid');
        $this->setDefaultSort('category_id');
        $this->setUseAjax(true);
        if ($this->getEvent() && $this->getEvent()->getId()) {
            $this->setDefaultFilter(['in_categories' => 1]);
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
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_categories') {
            $categoryIds = $this->_getSelectedCategories();
            if (empty($categoryIds)) {
                $categoryIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('category_id', ['in' => $categoryIds]);
            } else {
                if ($categoryIds) {
                    $this->getCollection()->addFieldToFilter('category_id', ['nin' => $categoryIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected items key
     *
     * @return array
     */
    protected function _getSelectedCategories()
    {
        $categories = array_keys($this->getSelectedCategories());
        return $categories;
    }

    /**
     * Retrieve selected items key
     *
     * @return array
     */
    public function getSelectedCategories()
    {
        $eventId = $this->getRequest()->getParam('event_id', 0);
        $event = $this->_eventFactory->create()->load($eventId);
        $categories = $event->getCategories();

        if (!$categories) {
            return [];
        }

        $categoryIds = [];

        foreach ($categories as $categoryId) {
            $categoryIds[$categoryId] = ['id' => $categoryId];
        }

        return $categoryIds;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToFilter('status', 1);
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
            'in_categories',
            [
                'type' => 'checkbox',
                'name' => 'in_categories',
                'values' => $this->_getSelectedCategories(),
                'align' => 'center',
                'index' => 'category_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'category_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'type' => 'number',
                'index' => 'category_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'category_title',
            [
                'header' => __('Title'),
                'type' => 'text',
                'index' => 'category_title',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => true,
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        return parent::_prepareColumns();
    }
}