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
 * @copyright 2014 Hitachi Capital
 * @license   http://www.gnu.org/copyleft/gpl.html GPL License
 * @link      http://www.cohesiondigital.co.uk/
 *
 */

/**
 * Session storage
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Session extends Mage_Core_Model_Session_Abstract
{
    /**
     * Constructor
     *
     * @return void.
     */
    public function __construct()
    {
        $this->init('paybyfinance');
    }

}
