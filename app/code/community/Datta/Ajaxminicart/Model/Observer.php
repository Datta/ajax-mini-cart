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
 * @category    Datta
 * @package     Datta_Ajaxminicart
 * @created     Dattatray Yadav  28th May, 2014 3:40pm
 * @author      Clarion magento team<Dattatray Yadav>  
 * @purpose     add to cart event observer and set response
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License   
 */    
class Datta_Ajaxminicart_Model_Observer {

    public function addToCartEvent($observer) {
     
        $request = Mage::app()->getFrontController()->getRequest();
           
        if (!$request->getParam('in_cart') && !$request->getParam('is_checkout')) {               
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
            $quote = Mage::getSingleton('checkout/cart')->getQuote();   
            $grandTotal = $quote->getGrandTotal(); 
            $subTotal = $quote->getSubtotal(); 
            $session= Mage::getSingleton('checkout/session');  
            $shippingTaxamount = Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount');    
            
            // get coupon discounted value            
            $totals = $quote->getTotals(); //Total object
            if(isset($totals['discount']) && $totals['discount']->getValue()) {
                $discount = Mage::helper('core')->currency($totals['discount']->getValue()); //Discount value if applied
            }else{
                $discount ='';
            } 
            //get discount value end                
            
            $html='';
            $productser = '';
            foreach($session->getQuote()->getAllVisibleItems() as $item)
            {  
               
               
               $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct()); 
               //print_r($productOptions);   
               if($productOptions){
                   if(isset($productOptions['options'])){
                        foreach($productOptions['options'] as $_option){
                             $productser = $_option['option_id'].','.$_option['option_value'];                           
                        } 
                   }
                    $superAttrString ='';
                    if(isset($productOptions['info_buyRequest']['super_attribute'])){
                        foreach($productOptions['info_buyRequest']['super_attribute'] as $key => $_superAttr){ 
                            $superAttrString .= '&super_attribute'.'['.$key.']='.$_superAttr;                                             
                        }                      
                    }
                    if($superAttrString):
                        $superAttrString.'&qty=1';
                    endif;
               }
              
               
               $productid = $item->getId();     
               $html .='<li id="li-'.$productid.'">';                 
               $product_id = $item->getProduct()->getId();
               $productsku = $item->getSku();
               $productname = $item->getName();               
               $productqty = $item->getQty();               
               $_product = Mage::getModel('catalog/product')->load($product_id); 
               $url = Mage::getUrl(
        'checkout/cart/deleteqty',
            array(
                'id'=>$productid,
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl()
                )
                );                
               $count = $quote->getItemsCount();                
               $html .='<div class="item-thumbnail">';                   
                     if ($item->hasProductUrl()):
                       $html .='<a href="'. $item->getUrl().'" title="'. $item->getName().'" class="product-image"><img src="'. Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail').'" width="50" height="50" alt="'. $this->escapeHtml($item->getName()).'" /></a>';
                    else:
                        $html .='<span><img src="'.Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail').'" width="50" height="50" alt="'.$item->getName() .'" /></span>';
                     endif; 
                $html .='</div>';
                $html .='<div class="mini-basket-content-wrapper">
                        <div class="mini-cart-name">
                        <a class="item-name" href="'.$item->getUrl().'">'.$productname.'</a>       
                    </div>  
                            <div class="qty-btngroup">
                                <button class="minus" type="button" onclick="setDeleteqty('.$productid.",'". $url."'".')">-</button>
                                 <input type="text" id="cart_qty_'.$productid.'" class="input-text qty" maxlength="12" title="Qty" size="4" value="'.$productqty.'" name="cart['.$productid.'][qty]" readonly="readonly">              
                                <button class="plus add-to-cart" type="button" onclick="setLocation('."'". Mage::getUrl('checkout/cart/add', array('product'=>$product_id)) ."?option=".$productser.$superAttrString."',".$productid.")".'">+</button>
                            </div>
                            
                        <span class="item-price">
                           &nbsp;&nbsp;x
                        
                                                    <span class="price">$'.$item->getPrice().'</span>                        
                        
                            </span> 
                                   </div> <a href="javascript:void(0);" id="'.$productid.'" onclick="setDeleteitem('.$productid.",'". $url."'".')" title="Remove item" class="remove-btn" ><span class="icon-remove"></span></a> 
                        <div class="clearfix"></div>';                   
              
               $html .='</li>';   
            } 
                              
            $_response = Mage::getModel('ajaxminicart/response')
                    ->setProductName($observer->getProduct()->getName())
                    ->setCarttotal($grandTotal)
                    ->setCartsubtotal($subTotal)
                    ->setCartcount($count) 
                    ->setDiscount($discount)
                    ->setShippingtaxamount($shippingTaxamount)
                    ->setCartitem($html)                                                                 
                    ->setMessage(Mage::helper('checkout')->__('%s was added into cart.', $observer->getProduct()->getName()));

            //append updated blocks
            $_response->addUpdatedBlocks($_response);

            $_response->send();
        
        }
        if ($request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('ajaxminicart/response')
                    ->setProductName($observer->getProduct()->getName())  
                    ->setMessage(Mage::helper('checkout')->__('%s was added into cart.', $observer->getProduct()->getName()));
            $_response->send();
        }
    }

    public function updateItemEvent($observer) {

        $request = Mage::app()->getFrontController()->getRequest();
         
        if (!$request->getParam('in_cart') && !$request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
            $quote = Mage::getSingleton('checkout/cart')->getQuote();   
            $grandTotal = $quote->getGrandTotal(); 
            $subTotal = $quote->getSubtotal();
            $shippingTaxamount = Mage::helper('checkout')->getQuote()->getShippingAddress()->getData('tax_amount');  
            
             // get coupon discounted value            
            $totals = $quote->getTotals(); //Total object
            if(isset($totals['discount']) && $totals['discount']->getValue()) {
                $discount = Mage::helper('core')->currency($totals['discount']->getValue()); //Discount value if applied
            }else{
                $discount ='';
            } 
            //get discount value end                
            $_response = Mage::getModel('ajaxminicart/response') 
                    ->setCarttotal($grandTotal)
                    ->setCartsubtotal($subTotal) 
                    ->setShippingtaxamount($shippingTaxamount)   
                    ->setDiscount($discount)                    
                    ->setMessage(Mage::helper('checkout')->__('Item was updated'));

            //append updated blocks
            $_response->addUpdatedBlocks($_response);

            $_response->send();
        }
        if ($request->getParam('is_checkout')) {

            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);

            $_response = Mage::getModel('ajaxminicart/response') 
                    ->setMessage(Mage::helper('checkout')->__('Item was updated'));
            $_response->send();
        }
    } 
    public function getConfigurableOptions($observer) {
        $is_ajax = Mage::app()->getFrontController()->getRequest()->getParam('ajax');

        if($is_ajax) {
            $_response = Mage::getModel('ajaxminicart/response');
            $_response = Mage::getModel('ajaxminicart/response');                         

            $product = Mage::registry('current_product');
            if (!$product->isConfigurable() && !$product->getTypeId() == 'bundle'){return false;exit;}
            //append configurable options block
            $_response->addConfigurableOptionsBlock($_response);
            $_response->send();
        }
        return;
    }
    public function getGroupProductOptions() {
        $id = Mage::app()->getFrontController()->getRequest()->getParam('product');
        $options = Mage::app()->getFrontController()->getRequest()->getParam('super_group');
        if($id) {
            $product = Mage::getModel('catalog/product')->load($id);
            if($product->getData()) {
                if($product->getTypeId() == 'grouped' && !$options) {
                    $_response = Mage::getModel('ajaxminicart/response');
                    Mage::register('product', $product);
                    Mage::register('current_product', $product);                            
                    //add group product's items block
                    $_response->addGroupProductItemsBlock($_response);
                    $_response->send();
                }
            }
        }
    }       
}