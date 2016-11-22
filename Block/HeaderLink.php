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
    protected $_moduleManager;
    protected $_eventsHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebuzz\Events\Helper\Data $eventsHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
        $this->_eventsHelper = $eventsHelper;
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
        return __('Event Calendar');
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
