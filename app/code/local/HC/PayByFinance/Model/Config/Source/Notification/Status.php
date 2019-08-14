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
 * @copyright 2015 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.healthywebsites.co.uk/
 *
 */

/**
 * Provides the list statuses sent by Hitachi Capital during notification.
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Model_Config_Source_Notification_Status
{
    /**
     * toOptionArray
     *
     * @return array Indexed array of options.
     */
    public function toOptionArray()
    {
        $options = array(
            'PAID' => 'PAID: The application has been paid.',
            'COOLING_OFF' => 'COOLING_OFF: The application is in a cooling off period.',
        );

        return $options;
    }
}
