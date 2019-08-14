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
 * Provides the list of product types to be used in admin config fields
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Config_Source_Type
{
    const TYPE25 = 25; //Deferred Interest Bearing Credit
    const TYPE31 = 31; //Interest bearing credit
    const TYPE32 = 32; //Interest free credit
    const TYPE33 = 33; //Interest option
    const TYPE34 = 34; //Buy now pay later interest free credit
    const TYPE35 = 35; //Buy now pay later interest bearing
    const TYPE36 = 36; //Accelerated Payment

    /**
     * toOptionArray
     *
     * @return array Indexed array of options.
     */
    public function toOptionArray()
    {
        $options = array(
            self::TYPE25 => self::TYPE25 . ': Deferred Interest Bearing Credit',
            self::TYPE31 => self::TYPE31 . ': Interest bearing credit',
            self::TYPE32 => self::TYPE32 . ': Interest free credit',
            self::TYPE33 => self::TYPE33 . ': Interest option',
            self::TYPE34 => self::TYPE34 . ': Buy now pay later interest free credit',
            self::TYPE35 => self::TYPE35 . ': Buy now pay later interest bearing',
            self::TYPE36 => self::TYPE36 . ': Accelerated Payment',
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
            self::TYPE25 => 'Deferred Interest Bearing',
            self::TYPE31 => 'Interest bearing',
            self::TYPE32 => 'Interest free',
            self::TYPE33 => 'Interest option',
            self::TYPE34 => 'Buy now pay later interest free',
            self::TYPE35 => 'Buy now pay later interest bearing',
            self::TYPE36 => 'Accelerated Payment',
        );

        return $options;
    }

    /**
     * Is provided service type deferred one? Those services requires
     * more parameters when sending to HC
     *
     * @param int $serviceType service Type
     *
     * @return bool
     */
    public static function isDeferredType($serviceType)
    {
        return ($serviceType == self::TYPE25) || ($serviceType == self::TYPE34)
        || ($serviceType == self::TYPE35);
    }

}
