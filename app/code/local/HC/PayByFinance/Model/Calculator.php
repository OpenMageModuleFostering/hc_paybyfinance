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
 * Data model for finance calculations
 *
 * @uses     Varien_Object
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Model_Calculator extends Varien_Object
{
    private $_service;

    /**
     * Set service by id
     *
     * @param integer $id ID
     *
     * @throws Exception in case the service does not exists
     *
     * @return self $this
     */
    public function setService($id)
    {
        $service = Mage::getModel('paybyfinance/service')->load($id);
        if (!$service) {
            throw new Exception("Requested service with id: $id does not exist", 1);
        }
        $this->_service = $service;

        return $this;
    }

    /**
     * Set service for unit tests (fake service object)
     *
     * @param Object $service Service object
     *
     * @return self $this
     */
    public function setTestService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Get Service
     *
     * @throws Exception In case it was not initialised by setService()
     *
     * @return HC_PayByFinance_Model_Service Service object
     */
    public function getService()
    {
        if (!$this->_service) {
            throw new Exception("You must set the service first", 1);
        }

        return $this->_service;
    }

    /**
     * Calculating monthly payments
     *
     * @param float $amount Credit amount
     *
     * @return float Monthly installment
     */
    public function calcMonthlyPayment($amount)
    {
        $service = $this->getService();
        // No rounding issues, rounding always down.
        $monthlyPayment = floor(($amount * $service->getMultiplier()) * 100) / 100;

        return $monthlyPayment;
    }

    /**
     * Calculating monthly payments (Interst Free, type=32)
     *
     * @param float $amount Credit amount
     *
     * @return float Monthly installment
     */
    public function calcMonthlyPaymentInterestFree($amount)
    {
        $service = $this->getService();
        $monthlyPayment = floor(($amount / $service->getTerm()) * 100) / 100;

        return $monthlyPayment;
    }

    /**
     * Calculate and get results
     *
     * @return Varien_Object Results
     */
    public function getResults()
    {
        $amount = $this->getAmount();
        $deposit = $this->getDeposit();
        $service = $this->getService();
        $discount = abs($this->getDiscount()); // Discount is a negative value
        $giftcard = abs($this->getGiftcard()); // Giftcard (EE) is a positive value

        $depositAmount = ($amount - $discount - $giftcard) * ($deposit / 100);
        $financeAmount = $amount - $depositAmount - $discount - $giftcard;

        if ($service->getType() == 32) {
            $monthlyPayment = $this->calcMonthlyPaymentInterestFree($financeAmount);
        } else {
            $monthlyPayment = $this->calcMonthlyPayment($financeAmount);
        }

        $results = new Varien_Object();
        $results
            ->setDeposit($depositAmount)
            ->setAmount($amount)
            ->setFinanceAmount($financeAmount)
            ->setMonthlyPayment($monthlyPayment);

        return $results;
    }
}
