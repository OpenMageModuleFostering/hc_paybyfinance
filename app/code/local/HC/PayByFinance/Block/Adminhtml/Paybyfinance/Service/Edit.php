<?php
/**
 * Hitachi Capital Pay By Finance
 *
 * Hitachi Capital Pay By Finance Extension
 *
 * PHP version >= 5.4.*
 *
 * @category  HC
 * @package   PayByFinance
 * @author    Cohesion Digital <support@cohesiondigital.co.uk>
 * @copyright 2014 Cohesion Digital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */

/**
 * Edit form container for service
 *
 * @uses     Mage_Adminhtml_Block_Widget_Form_Container
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Block_Adminhtml_Paybyfinance_Service_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     *
     * @return mixed Value.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_paybyfinance_service';
        $this->_blockGroup = 'paybyfinance';
        $helper = Mage::helper('paybyfinance');

        $this->_updateButton('save', 'label', $helper->__('Save Service'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Service'));
    }

    /**
     * Get Header Text
     *
     * @return string Header text.
     */
    public function getHeaderText()
    {
        $service = Mage::registry('service_data');
        if ($service && $service->getId() ) {
            return Mage::helper('paybyfinance')->__(
                "Edit Service '%s'",
                $this->htmlEscape(Mage::registry('service_data')->getName())
            );
        } else {
            return Mage::helper('paybyfinance')->__('Add Service');
        }
    }
}
