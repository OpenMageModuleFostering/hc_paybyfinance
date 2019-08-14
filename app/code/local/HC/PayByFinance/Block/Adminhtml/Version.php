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
 * Version information to be displayed on the configuration
 *
 * @uses     Mage_Adminhtml_Block_Template
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Block_Adminhtml_Version extends Mage_Adminhtml_Block_Template
{
    const PATCH_LEVEL = 1;

    protected $_template = "paybyfinance/version.phtml";

     /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element Element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * Get module version
     *
     * @return string Module version
     */
    private function getVersion()
    {
        $ver = Mage::getConfig()->getNode('modules/HC_PayByFinance/version');
        $ver .= '.'.self::PATCH_LEVEL;
        return $ver;
    }

    /**
     * Get module version html
     *
     * @return string Module version as html
     */
    public function getVersionHtml()
    {
        $res = '';
        $ver = $this->getVersion();
        $res .= '<h3>Hitachi Capital Pay By Finance ' . $ver . '</h3>';

        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        if (array_key_exists('Enterprise_Enterprise', $modules)) {
            $aux = 'Enterprise Edition';
            $saux = 'EE';
        } else {
            $aux = 'Community Edition';
            $saux = 'CE';
        }
        $mageVersion = Mage::getVersion();
        $mage = "Magento {$aux} {$mageVersion}";

        $id = $saux . $mageVersion . '_' . $ver;
        $hash = substr(md5($id), 0, 12);

        $res .= '<h4>' . $mage .'</h4>';

        $res .= '<p>';
        $res .= 'Support reference: ' . $hash;
        $res .= '<br/><a href="mailto:support@healthywebsites.co.uk?subject=Support&body='
            . $id . '%20ref:%20' . $hash . '%0D%0ADO NOT REMOVE THE ABOVE LINE'
            . '">Email support</a>';
        $res .= '</p>';

        return $res;
    }

}
