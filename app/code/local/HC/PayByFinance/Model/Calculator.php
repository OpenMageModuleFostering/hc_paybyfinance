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
 * Data model for finance calculations
 *
 * @uses     Varien_Object
 *
 * @category HC
 * @package  PayByFinance
 * @author   Cohesion Digital <support@cohesiondigital.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.cohesiondigital.co.uk/
 */
class HC_PayByFinance_Model_Calculator extends Varien_Object
{
    private $_service;
    private $_allServices;

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
     * @param float                         $amount  Credit amount
     * @param HC_PayByFinance_Model_Service $service Service
     *
     * @return float Monthly installment
     */
    public function calcMonthlyPayment($amount, $service = null)
    {
        if ($service === null) {
            $service = $this->getService();
        }
        // rounding always up for interest bearing services.
        $monthlyPayment = ceil(($amount * $service->getMultiplier()) * 100) / 100;

        return $monthlyPayment;
    }

    /**
     * Calculating monthly payments (Interest Free, type=32)
     *
     * @param float                         $amount  Credit amount
     * @param HC_PayByFinance_Model_Service $service Service
     *
     * @return float Monthly installment
     */
    public function calcMonthlyPaymentInterestFree($amount, $service = null)
    {
        if ($service === null) {
            $service = $this->getService();
        }
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
        $depositAmount = round($depositAmount, 2);
        $financeAmount = $amount - $depositAmount - $discount - $giftcard;
        // Rounding issues workaround: convert to string first.
        // Note it would be better to use BCMath as an additional dependency.
        $financeAmount = intval((string) ($financeAmount * 100)) / 100;

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

    /**
     * Get cached all services collection instance
     *
     * @return HC_PayByFinance_Model_Mysql4_Service_Collection Services collection
     */
    public function getAllServices()
    {
        if (!$this->_allServices) {
            $this->_allServices = Mage::getModel('paybyfinance/service')
                ->getCollection()
                ->storeFilter(Mage::app()->getStore()->getStoreId());
        }

        return $this->_allServices;
    }

    /**
     * Get lowest monthly installment based on product price from all available services
     *
     * @param float $price Product price
     *
     * @return float|bool Cheapest monthly installment, false if no service available
     */
    public function getLowestMonthlyInstallment($price)
    {
        $minInstallment = false;
        foreach ($this->getAllServices() as $service) {
            $depositAmount = ($price) * ($service->getDeposit() / 100);
            $depositAmount = round($depositAmount, 2);
            $financeAmount = $price - $depositAmount;
            $financeAmount = intval((string) ($financeAmount * 100)) / 100;
            if ($financeAmount < $service->getMinAmount()) {
                continue;
            }
            if (($service->getMaxAmount() !== null) && $financeAmount > $service->getMaxAmount()) {
                continue;
            }

            if ($service->getType() == 32) {
                $installment = $this->calcMonthlyPaymentInterestFree($financeAmount, $service);
            } else {
                $installment = $this->calcMonthlyPayment($financeAmount, $service);
            }

            if ($minInstallment === false || $minInstallment > $installment) {
                $minInstallment = $installment;
            }
        }

        return $minInstallment;
    }
}
