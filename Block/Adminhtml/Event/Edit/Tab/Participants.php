<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab;

class Participants extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_eventFactory;
    protected $_eventsProductFactory;

    protected $_participantFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebuzz\Events\Model\EventFactory $eventFactory,
        \Magebuzz\Events\Model\Catalog\ProductFactory $eventsProductFactory,
        \Magebuzz\Events\Model\ParticipantFactory $participantFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        $this->_eventFactory = $eventFactory;
        $this->_eventsProductFactory = $eventsProductFactory;
        $this->_participantFactory = $participantFactory;
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
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/participantgrid', ['_current' => true]);
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
        $this->setDefaultSort($this->getEvent()->getPrice() > 0 ? 'entity_id' : 'participant_id');
        $this->setUseAjax(true);
    }

    protected function getEvent()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        return $this->_eventFactory->create()->load($eventId);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $event = $this->getEvent();
        $eventPrice = $event->getPrice();
        $collection = null;
        if ($eventPrice > 0) {
            $productId = (int)$event->getProductId();
            if ($productId && $productId > 0) {
                $product = $this->_eventsProductFactory->create()->load($productId);
                if ($product->getId()) {
                    $collection = $product->getOrdererAddressCollection();
                }
            }
        } else {
            $eventId = $this->getRequest()->getParam('event_id');
            $collection = $this->_participantFactory->create()->getCollection()
                ->addFieldToFilter('event_id', $eventId)
                ->addFieldToFilter('status', 1);
        }
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
        $price = $this->getEvent()->getPrice();
        $this->addColumn(
            'participant_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'type' => 'number',
                'index' => $price > 0 ? 'entity_id' : 'participant_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $fullnameRenderer = $price > 0 ? 'Magebuzz\Events\Block\Adminhtml\Grid\Column\Renderer\Fullname' : '';
        $this->addColumn(
            'participant_fullname',
            [
                'header' => __('Full Name'),
                'index' => $price > 0 ? 'fullname' : 'fullname',
                'header_css_class' => 'col-fullname',
                'column_css_class' => 'col-fullname',
                'renderer' => $fullnameRenderer
            ]
        );
        $this->addColumn(
            'participant_phone',
            [
                'header' => __('Phone'),
                'index' => $price > 0 ? 'telephone' : 'phone',
                'header_css_class' => 'col-phone',
                'column_css_class' => 'col-phone'
            ]
        );
        $this->addColumn(
            'participant_email',
            [
                'header' => __('Email'),
                'index' => $price > 0 ? 'email' : 'email',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );
        $locationRenderer = $price > 0 ? 'Magebuzz\Events\Block\Adminhtml\Grid\Column\Renderer\Location' : '';
        $this->addColumn(
            'participant_address',
            [
                'header' => __('Address'),
                'index' => $price > 0 ? 'location' : 'address',
                'header_css_class' => 'col-address',
                'column_css_class' => 'col-address',
                'renderer' => $locationRenderer
            ]
        );

        return parent::_prepareColumns();
    }

}