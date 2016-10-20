<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

class Participants extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_eventFactory;

    protected $_participantFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magebuzz\Events\Model\ParticipantFactory $participantFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_participantFactory = $participantFactory;
        $this->_coreRegistry = $coreRegistry;
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
        $this->setId('events_participant_grid');
        $this->setDefaultSort('participant_id');
        $this->setUseAjax(true);
    }


    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        $collection = $this->_participantFactory->create()->getCollection()
                ->addFieldToFilter('event_id', $eventId)
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
            'participant_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'participant_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'fullname',
            [
                'header' => __('Full Name'),
                'index' => 'fullname',
                'header_css_class' => 'col-fullname',
                'column_css_class' => 'col-fullname'
            ]
        );
        $this->addColumn(
            'phone',
            [
                'header' => __('Phone'),
                'index' => 'phone',
                'header_css_class' => 'col-phone',
                'column_css_class' => 'col-phone'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );
        $this->addColumn(
            'address',
            [
                'header' => __('Address'),
                'index' => 'address',
                'header_css_class' => 'col-address',
                'column_css_class' => 'col-address'
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
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/participantgrid',['_current' => true]);
    }
    
}