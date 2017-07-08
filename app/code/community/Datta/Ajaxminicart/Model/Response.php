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
 * @package     Datta_Ajaxcart
 * @created     Dattatray Yadav  28th May, 2014 3:40pm
 * @author      Clarion magento team<Dattatray Yadav>  
 * @purpose     send response and update blocks
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License   
 */
class Datta_Ajaxminicart_Model_Response extends Mage_Catalog_Block_Product_Abstract {

    public function send() {
        Zend_Json::$useBuiltinEncoderDecoder = true;
        if ($this->getError())
            $this->setR('error');
        else
            $this->setR('success');
        Mage::app()->getFrontController()->getResponse()->setHeader('Content-Type', 'text/plain')->setBody(Zend_Json::encode($this->getData()));
        Mage::app()->getFrontController()->getResponse()->sendResponse();
        die;
    }

    public function addUpdatedBlocks(&$_response) {
        $updated_blocks = unserialize(Mage::getStoreConfig('ajaxminicart/general/update_blocks'));

        if ($updated_blocks) {
            $layout = Mage::getSingleton('core/layout');
            $res = array();  
            foreach ($updated_blocks['id'] as $index => $block) {
                $value = $layout->getBlock($updated_blocks['xml'][$index]); 
                if ($value) {
                    $tmp['key'] = $block;
                    $tmp['value'] = $value->toHtml();
                    $res[] = $tmp;
                }
            }
            if (!empty($res)) {
                $_response->setUpdateBlocks($res);
            }
        }
    }

    public function addConfigurableOptionsBlock(&$_response) {
        $layout = Mage::getSingleton('core/layout');
        $res = '';
        $_product = Mage::registry('current_product');

        $layout->getUpdate()->addHandle('ajaxminicart_configurable_options');
        
        if ($_product->getTypeId() == 'bundle')
        $layout->getUpdate()->addHandle('ajaxminicart_bundle_options');        

        // set unique cache ID to bypass caching
        $cacheId = 'LAYOUT_'.Mage::app()->getStore()->getId().md5(join('__', $layout->getUpdate()->getHandles()));
        $layout->getUpdate()->setCacheId($cacheId);  
        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateBlocks();               
        $value = $layout->getBlock('ajaxminicart.configurable.options');       
        
        if ($value) {
            $res .= $value->toHtml();
        }         
        if ($_product->getTypeId() == 'bundle') {
            $value = $layout->getBlock('product.info.bundle');        
            
            if ($value) {
                $res .= $value->toHtml();
            }
        }          
        if (!empty($res)) {
            $_response->setConfigurableOptionsBlock($res);
        }
    } 
    public function addGroupProductItemsBlock(&$_response) {
        $layout = Mage::getSingleton('core/layout');
        $res = '';
        $layout->getUpdate()->addHandle('ajaxminicart_grouped_options');
        // set unique cache ID to bypass caching
        $cacheId = 'LAYOUT_'.Mage::app()->getStore()->getId().md5(join('__', $layout->getUpdate()->getHandles()));
        $layout->getUpdate()->setCacheId($cacheId);                     
        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateBlocks();                                      
        $value = $layout->getBlock('ajaxminicart.grouped.options');     
        if ($value) {
            $res .= $value->toHtml();
        }                                                               
        if (!empty($res)) {
            $_response->setConfigurableOptionsBlock($res);
        }
    }                                                                       
}