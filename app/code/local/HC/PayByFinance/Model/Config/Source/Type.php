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
class HC_PayByFinance_Model_Config_Source_Type
{
    /**
     * toOptionArray
     *
     * @return array Indexed array of options.
     */
    public function toOptionArray()
    {
        $options = array(
            31 => '31: Interest bearing credit',
            32 => '32: Interest free credit',
            33 => '33: Interest option',
            34 => '34: Buy now pay later interest free credit',
            35 => '35: Buy now pay later interest bearing',
        );

        return $options;
    }

    /**
     * For names useful to diplay in the frontend
     *
     * @return array Indexed array of options.
     */
    public function toFrontendArray()
    {
        $options = array(
            31 => 'Interest bearing',
            32 => 'Interest free',
            33 => 'Interest option',
            34 => 'Buy now pay later interest free',
            35 => 'Buy now pay later interest bearing',
        );

        return $options;
    }
}
