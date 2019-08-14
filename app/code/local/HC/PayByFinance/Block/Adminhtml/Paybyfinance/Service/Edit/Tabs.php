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
 * Tabs widget for service edit form
 *
 * @uses     Mage_Adminhtml_Block_Widget_Tabs
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFInance_Block_Adminhtml_Paybyfinance_Service_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor.
     *
     * @return mixed Value.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('service_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(
            Mage::helper('paybyfinance')->__('Service Information')
        );
    }

    /**
     * _beforeToHtml
     *
     * @return mixed Value.
     */
    protected function _beforeToHtml()
    {
        $helper = Mage::helper('paybyfinance');
        $blockId = 'paybyfinance/adminhtml_paybyfinance_service_edit_tab_form';
        $this->addTab(
            'form_section', array(
                'label' => $helper->__('Service Information'),
                'title' => $helper->__('Service Information'),
                'content' => $this->getLayout()
                    ->createBlock($blockId)->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }
}
