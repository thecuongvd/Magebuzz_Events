<?php
namespace Magebuzz\Events\Controller\Index;

use Magento\Framework\App\Action\Action;

class Addtocart extends Action
{
    protected $resultPageFactory;
    protected $_productFactory;
    protected $_cart;

    public function __construct(\Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Checkout\Model\Cart $cart
    ) { 
        $this->resultPageFactory = $resultPageFactory;
        $this->_productFactory = $productFactory;
        $this->_cart = $cart;
        parent::__construct($context);
    }
    
    public function execute()
    {  
        $productId = $this->getRequest()->getParam('product');
        $formKey = $this->getRequest()->getParam('formkey');
        $params = array(
            'product' => $productId,
            'formkey' => $formKey,
            'qty' => 1
        );
        $product = $this->_productFactory->create()->load($productId);
        $this->_cart->addProduct($product,$params);
        $this->_cart->save();

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }

}
