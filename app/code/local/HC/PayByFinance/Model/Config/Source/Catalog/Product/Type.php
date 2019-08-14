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
 * @copyright 2014 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.healthywebsites.co.uk/
 *
 */

/**
 * Provides the list of product types to be used in admin config fields
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Model_Config_Source_Catalog_Product_Type
{
    protected $_options;

    /**
     * toOptionArray
     *
     * @return array Indexed array of options.
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage_Catalog_Model_Product_Type::getAllOptions();
            unset($this->_options[0]);
        }
        $options = $this->_options;

        return $options;
    }
}
