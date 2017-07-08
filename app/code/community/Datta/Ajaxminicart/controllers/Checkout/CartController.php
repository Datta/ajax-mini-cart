<?php
/**
 * Magento 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *  
 * 
 * @category    Datta
 * @package     Datta_Ajaxminicart
 * @created     Dattatray Yadav  28th May, 2014 2:00pm
 * @author      Clarion magento team<Dattatray Yadav>    
 * @purpose     Manage ajax cart add action and return result
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License   
 */
 
require_once 'Mage/Checkout/controllers/CartController.php';
class Datta_Ajaxminicart_Checkout_CartController extends Mage_Checkout_CartController {

    /**
     * Add product to shopping cart action
     */
    public function addAction() {                     
        
        $cart = $this->_getCart();       
        $params = $this->getRequest()->getParams();   
        
        try {   
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            } 
            if (isset($params['options'])) {                    
                $params['options'] = $params['options'];                 
            }                                                                                                    
            $product = $this->_initProduct();        
           
            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }
			
            $cart->addProduct($product, $params);           			
            $cart->save();   
            $this->applyCoupon(); 
            $this->_getSession()->setCartWasUpdated(true);
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                      
            $this->getLayout()->getUpdate()->addHandle('ajaxminicart');
            $this->loadLayout();

            Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {            
            $_response = Mage::getModel('ajaxminicart/response');
            $_response->setError(true);

            $messages = array_unique(explode("\n", $e->getMessage()));
            $json_messages = array();
            foreach ($messages as $message) {
                $json_messages[] = Mage::helper('core')->escapeHtml($message);
            }                                        
            $_response->setMessages($json_messages); 
            $url = $this->_getSession()->getRedirectUrl(true);
            $_response->send();
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);              
            $_response = Mage::getModel('ajaxminicart/response');
            $_response->setError(true);
            $_response->setMessage($this->__('Cannot add the item to shopping cart.'));
            $_response->send();
        }
    }           
    /*
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction() {
        $cart = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }
            $this->_getSession()->setCartWasUpdated(true);             
            $cart->save();  
            $this->applyCoupon();
            
            $this->getLayout()->getUpdate()->addHandle('ajaxminicart');
            $this->loadLayout();

            Mage::dispatchEvent('checkout_cart_update_item_complete', array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            $_response = Mage::getModel('ajaxminicart/response');
            $_response->setError(true);

            $messages = array_unique(explode("\n", $e->getMessage()));
            $json_messages = array();
            foreach ($messages as $message) {
                $json_messages[] = Mage::helper('core')->escapeHtml($message);
            }
            $_response->setMessages($json_messages);
            $url = $this->_getSession()->getRedirectUrl(true);
            $_response->send();
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);             
            $_response = Mage::getModel('ajaxminicart/response');
            $_response->setError(true);
            $_response->setMessage($this->__('Cannot update the item.'));
            $_response->send();
        }
    }          
    /**
     * Delete shoping cart item action
     */
    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('id');     
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                        ->save();  
                 
            } catch (Exception $e) {
                $_response = Mage::getModel('ajaxminicart/response');
                $_response->setError(true);
                $_response->setMessage($this->__('Error...Cannot remove the item.'));
                $_response->send();
                Mage::logException($e);
            }
        }  
         Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        $quote = Mage::getSingleton('checkout/cart')->getQuote(); 
        $shippingTaxamount = Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount'); 
        // get coupon discounted value            
        $totals = $quote->getTotals(); //Total object
        if(isset($totals['discount']) && $totals['discount']->getValue()) {
            $discount = Mage::helper('core')->currency($totals['discount']->getValue()); //Discount value if applied
        }else{
            $discount ='';
        } 
        //get discount value end                
        $count = $quote->getItemsCount();  
        $grandTotal = $quote->getGrandTotal();
        $subTotal = $quote->getSubtotal();
        $_response = Mage::getModel('ajaxminicart/response');
        $_response->setCarttotal($grandTotal); 
        $_response->setCartsubtotal($subTotal); 
        $_response->setDiscount($discount);           
        $_response->setShippingtaxamount($shippingTaxamount);
        $_response->setCartcount($count);
        $_response->setMessage($this->__('Item was removed'));
        //append updated blocks
        $this->getLayout()->getUpdate()->addHandle('ajaxminicart');
        $this->loadLayout();
        $_response->addUpdatedBlocks($_response);
        $_response->send();
    }
    public function deleteqtyAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $qty = (int) $this->getRequest()->getParam('qty');
        $message ='';  
        $_response = Mage::getModel('ajaxminicart/response');  
        $quote = Mage::getSingleton('checkout/session')->getQuote();  
        if ($id) {
            try {                   
              $product = Mage::getModel('catalog/product')->load($id);               
              if($qty>=1){
               $cartItems = $quote->getAllVisibleItems(); 
               $cart = Mage::getSingleton('checkout/cart');    
               foreach ($cartItems as $item) {
                    if($id==$item->getId()){
                        $item->setQty($qty);                   
                        $cart->save();
                        $message =  $this->__('Item qty was removed.'); 
                    }                      
                }    
               //get Item                                                
               }else{
                  $this->_getCart()->removeItem($id)
                    ->save();   
                   $message =  $this->__('Item was removed.'); 
               }                                                                 
            } catch (Exception $e) {
                $_response = Mage::getModel('ajaxminicart/response');
                $_response->setError(true);
                $_response->setMessage($this->__('Error...Cannot remove the item.'));
                $_response->send();
                Mage::logException($e);
            }
        }
        Mage::getSingleton('checkout/session')->setNoCartRedirect(true);          
        $grandTotal = $quote->getGrandTotal();
        $subTotal = $quote->getSubtotal(); 
        $count = $quote->getItemsCount();        
        $shippingTaxamount = Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount'); 
        // get coupon discounted value            
            $totals = $quote->getTotals(); //Total object
            if(isset($totals['discount']) && $totals['discount']->getValue()) {
                $discount = Mage::helper('core')->currency($totals['discount']->getValue()); //Discount value if applied
            }else{
                $discount ='';
            } 
        //get discount value end             
        $_response->setCarttotal($grandTotal);
        $_response->setCartcount($count);
        $_response->setDiscount($discount);
        $_response->setShippingtaxamount($shippingTaxamount);
        $_response->setCartsubtotal($subTotal); 
        $_response->setMessage($message);
        //append updated blocks
        $this->getLayout()->getUpdate()->addHandle('ajaxminicart');
        $this->loadLayout();             
        $_response->addUpdatedBlocks($_response);
        $_response->send();
    }
    public function applyCoupon(){            
       $coupon_code = Mage::getSingleton('checkout/session')->getQuote()->getCouponCode(); 
       if($coupon_code){ 
          Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon_code)->save();
       }
    } 
}