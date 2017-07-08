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
 * @created     Dattatray Yadav  28th May, 2014 1:45pm
 * @author      Clarion magento team<Dattatray Yadav>    
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License   
 */

class Datta_Ajaxminicart_Block_Adminhtml_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {
    protected function _getInfo($content) {
        $output = $this->_getStyle();
        $output .= '<div class="creativestyle-info">';
        $output .= $content;
        $output .= '</div>';
        return $output;
    }   
    protected function _getStyle() {
        $content = '<style>';
        $content .= '.creativestyle-info { border: 1px solid #cccccc; background: #e7efef; margin-bottom: 10px; padding: 10px; height: auto; }';
        $content .= '.creativestyle-info .creativestyle-logo { float: right; padding: 5px; }';
        $content .= '.creativestyle-info .creativestyle-command { border: 1px solid #cccccc; background: #ffffff; padding: 15px; text-align: left; margin: 10px 0; font-weight: bold;         }';
        $content .= '.creativestyle-info h3 { color: #ea7601; }';
        $content .= '.creativestyle-info h3 small { font-weight: normal; font-size: 80%; font-style: italic; }';
        $content .= '</style>';
        return $content;
    }   
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $content = '<h3></h3>';
        return $this->_getInfo($content);
    }      
}