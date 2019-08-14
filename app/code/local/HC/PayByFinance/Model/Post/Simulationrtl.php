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
 * Hitachi Post Simulation Model
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Model_Post_Simulationrtl extends HC_PayByFinance_Model_Post_Abstract
{
    protected $_postUrl   =
        'https://rtl-www.creditmaster2.co.uk/Ecommerce/etailer/createQuote.action';
    protected $_notifyUrl = 'https://rtl-www.creditmaster2.co.uk/Ecommerce/etailer/notify.action';
    protected $_mode      = 'simulation';
}
