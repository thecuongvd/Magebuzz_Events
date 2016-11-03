<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Block;

/**
 * "My Ticket" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class HeaderLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magebuzz\Events\Helper\Data
     */
    protected $_eventsHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magebuzz\Events\Helper\Data $eventsHelper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        array $data = []
    ) { 
        parent::__construct($context, $data);
        $this->_eventsHelper = $eventsHelper;
        $this->_moduleManager = $moduleManager;
        $this->_customerSession = $customerSession;
    }

    public function isCustomerLoggedIn()
    {
        return (boolean)$this->_customerSession->isLoggedIn();
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getHref()
    {
        return $this->getUrl('events', ['_secure' => true]);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getLabel()
    {
        return __('Events Calendar');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_eventsHelper->isHeaderlinkEnabled() || !$this->_moduleManager->isOutputEnabled(
            'Magebuzz_Events'
        )
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
