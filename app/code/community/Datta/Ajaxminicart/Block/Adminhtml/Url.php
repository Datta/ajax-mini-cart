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

class Datta_Ajaxminicart_Block_Adminhtml_Url extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $output = '<script type="text/javascript">//<![CDATA[' . "\n";
        $output .= '    var xml_form_template = \'' . str_replace("'", "\'", $this->_getRowEditHtml()) .'\';' . "\n";
        $output .= '//]]></script>' . "\n";
        $output .= '<input type="hidden" name="' . $this->getElement()->getName() . '" value="">';
        $output .= '<table id="xml_container" style="border-collapse:collapse;"><tbody>';
        $output .= $this->_getHeaderHtml();
        if ($this->getElement()->getData('value')) {
            foreach ($this->getElement()->getData('value/id') as $elementIndex => $elementName) {
                $output .= $this->_getRowHtml($elementIndex);
            }
        }
        $output .= '<tr><td colspan="2" style="padding: 4px 0;">';
        $output .= $this->_getAddButtonHtml();
        $output .= '</td></tr>';
        $output .= '</tbody></table>';
        return $output;
    }   
    protected function _getHeaderHtml() {
        $output = '<tr>';
        $output .= '<th style="padding: 2px; text-align: center;">';
        $output .= Mage::helper('ajaxminicart')->__('ID/Class selector of block to be updated');
        $output .= '</th>';
        $output .= '<th style="padding: 2px; text-align: center;">';
        $output .= Mage::helper('ajaxminicart')->__('Layout Update Block name(should be same as in XML)');
        $output .= '</th>';
        $output .= '<th>&nbsp;</th>';
        $output .= '</tr>';
        return $output;
    }   
    protected function _getRowHtml($index = 0) {
        $output = '<tr>';
        $output .= '<td style="padding: 2px 0;">';
        $output .= '<input type="text" class="required-entry input-text" style="margin-right:10px" name="' . $this->getElement()->getName() . '[id][]" value="' . $this->getElement()->getData('value/id/' . $index) . '" />';
        $output .= '</td>';
        $output .= '<td style="padding: 2px 0;">';
        $output .= '<input class="required-entry input-text" name="' . $this->getElement()->getName() . '[xml][]" value="'.$this->getElement()->getData('value/xml/' . $index) . '">';
        $output .= '</td>';
        $output .= '<td style="padding: 2px 4px;">';
        $output .= $this->_getRemoveButtonHtml();
        $output .= '</td>';
        $output .= '</tr>';
        return $output;
    }   
    protected function _getRowEditHtml() {
        $output = '<tr>';
        $output .= '<td style="padding: 2px 0;">';
        $output .= '<input class="required-entry input-text" style="margin-right:10px" name="' . $this->getElement()->getName() . '[id][]" />';
        $output .= '</td>';
        $output .= '<td style="padding: 2px 0;">';
        $output .= '<input class="required-entry input-text" name="' . $this->getElement()->getName() . '[xml][]">';
        $output .= '</td>';
        $output .= '<td style="padding: 2px 4px;">';
        $output .= $this->_getRemoveButtonHtml();
        $output .= '</td>';
        $output .= '</tr>';
        return $output;
    }   
    protected function _getAddButtonHtml() {
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('add')
            ->setLabel($this->__('Add Layout Update Block'))
            ->setOnClick("Element.insert($(this).up('tr'), {before: xml_form_template})")
            ->toHtml();
    }   
    protected function _getRemoveButtonHtml() {
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('delete v-middle')
            ->setLabel($this->__('Delete'))
            ->setOnClick("Element.remove($(this).up('tr'))")
            ->toHtml();
    }
}
