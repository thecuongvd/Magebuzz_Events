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
class TopLink extends \Magento\Framework\View\Element\Html\Link
{
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getHref()
    {
        return $this->getUrl('events/wishlist/index');
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getLabel()
    {
        return __('My Events');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_moduleManager->isOutputEnabled(
            'Magebuzz_Events'
        )
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
