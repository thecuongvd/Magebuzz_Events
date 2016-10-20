<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Translate\InlineInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('events_event_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Event'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Event Information'),
                'content' => $this->getLayout()->createBlock(
                    'Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab\Main'
                )->toHtml()
            ]
        );
        $this->addTab(
            'gallery',
            [
                'label' => __('Event Gallery'),
                'content' => $this->getLayout()->createBlock(
                    'Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab\Gallery'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'organizer',
            [
                'label' => __('Event Organizer Information'),
                'content' => $this->getLayout()->createBlock(
                    'Magebuzz\Events\Block\Adminhtml\Event\Edit\Tab\Contact'
                )->toHtml()
            ]
        );
        $this->addTab(
            'category',
            [
                'label' => __('Category'),
                'url' => $this->getUrl('events/*/categorygrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'product',
            [
                'label' => __('Associated Product'),
                'url' => $this->getUrl('events/*/productgrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'register_user',
            [
                'label' => __('Registered Users'),
                'url' => $this->getUrl('events/*/participantgrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_prepareLayout();
    }

    public function getEvent()
    {
        if (!$this->getData('events_event') instanceof \Magebuzz\Events\Model\Event) {
            $this->setData('events_event', $this->_coreRegistry->registry('events_event'));
        }
        return $this->getData('events_event');
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }
}