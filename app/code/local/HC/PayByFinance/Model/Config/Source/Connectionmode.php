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
* @license   http://www.healthywebsites.co.uk/license.html HWS License
* @link      http://www.healthywebsites.co.uk/
*
*/

/**
* Provides the list of product types to be used in admin config fields
*
* @category HC
* @package  PayByFinance
* @author   Healthy Websites <support@healthywebsites.co.uk>
* @license  http://www.healthywebsites.co.uk/license.html HWS License
* @link     http://www.healthywebsites.co.uk/
*/
class HC_PayByFinance_Model_Config_Source_Connectionmode
{
    /**
     * toOptionArray
     *
     * @return array Indexed array of options.
     */
    public function toOptionArray()
    {
        $options = array(
            'test' => 'Test',
            'simulation' => 'Simulation',
            'live' => 'Live',
        );

        return $options;
    }

}
