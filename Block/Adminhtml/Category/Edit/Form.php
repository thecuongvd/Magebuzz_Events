<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
 
namespace Magebuzz\Events\Block\Adminhtml\Category\Edit;
 
/**
 * Adminhtml edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
 
    protected $_systemStore;
 
    protected $_status;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_form');
        $this->setTitle(__('Category Information'));
    }
 
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('events_category');
 
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
 
        $form->setHtmlIdPrefix('category_');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
 
        if ($model->getId()) {
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
        }
 
        $fieldset->addField(
            'category_title',
            'text',
            ['name' => 'category_title', 'label' => __('Title'), 'title' => __('Title'), 'required' => true]
        );
        $fieldset->addField(
            'category_description',
            'editor',
            ['name' => 'category_description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height:18em']
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        
        if (!$model->getId()) {
            $model->setData('status', '1');
        }
        
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
 
        return parent::_prepareForm();
    }
}