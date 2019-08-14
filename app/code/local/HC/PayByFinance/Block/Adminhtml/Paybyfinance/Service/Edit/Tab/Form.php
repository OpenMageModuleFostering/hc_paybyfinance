<?php
/**
* Hitachi Capital Pay By Finance
*
* Hitachi Capital Pay By Finance Extension
*
* PHP version >= 5.3.*
*
* @category  HC
* @package   PayByFinance
* @author    Healthy Websites <support@healthywebsites.co.uk>
* @copyright 2014 Healthy Websites
* @license   http://www.gnu.org/copyleft/gpl.html GPL License
* @link      http://www.healthywebsites.co.uk/
*
*/

/**
* Data form for service
*
* @uses     Mage_Adminhtml_Block_Widget_Form
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.gnu.org/copyleft/gpl.html GPL License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Block_Adminhtml_Paybyfinance_Service_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     *
     * @return mixed Value.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $helper = Mage::helper('paybyfinance');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'service_form',
            array('legend' => $helper->__('Service information'))
        );

        $fieldset->addField(
            'name', 'text', array(
                'label'     => $helper->__('Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'name',
            )
        );

        $fieldset->addField(
            'type', 'select', array(
                'label'     => $helper->__('Service Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'type',
                'values' => Mage::getSingleton('paybyfinance/config_source_type')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'apr', 'text', array(
                'label'     => $helper->__('APR'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'apr',
            )
        );

        $fieldset->addField(
            'term', 'text', array(
                'label'     => $helper->__('Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'term',
            )
        );

        $fieldset->addField(
            'defer_term', 'text', array(
                'label'     => $helper->__('Defer Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'defer_term',
            )
        );

        $fieldset->addField(
            'option_term', 'text', array(
                'label'     => $helper->__('Option Term'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'option_term',
            )
        );

        $fieldset->addField(
            'deposit', 'text', array(
                'label'     => $helper->__('Deposit (%)'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'deposit',
            )
        );

        $fieldset->addField(
            'fee', 'text', array(
                'label'     => $helper->__('Fee'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'fee',
            )
        );

        $fieldset->addField(
            'min_amount', 'text', array(
                'label'     => $helper->__('Minimum Amount'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'min_amount',
            )
        );

        $fieldset->addField(
            'multiplier', 'text', array(
                'label'     => $helper->__('Multiplier'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'multiplier',
            )
        );

        $fieldset->addField(
            'rpm', 'text', array(
                'label'     => $helper->__('Rpm'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'rpm',
            )
        );

        if ( Mage::getSingleton('adminhtml/session')->getServiceData() ) {
            $form->setValues(
                Mage::getSingleton('adminhtml/session')->getServiceData()
            );
            Mage::getSingleton('adminhtml/session')->setServiceData(null);
        } elseif ( Mage::registry('service_data') ) {
            $form->setValues(Mage::registry('service_data')->getData());
        }
        return parent::_prepareForm();
    }
}
