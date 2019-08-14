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
 * Block class for status pages
 *
 * @uses     Mage_Core_Block_Template
 *
 * @category HC
 * @package  PayByFinance
 * @author   Healthy Websites <support@healthywebsites.co.uk>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL License
 * @link     http://www.healthywebsites.co.uk/
 */
class HC_PayByFinance_Block_Status extends Mage_Core_Block_Template
{
    private $_type;
    private $_order;

    /**
     * Set block type
     *
     * @param string $type Block type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Get CMS block based on layout xml
     *
     * @return string HTML output.
     *
     * @throws Exception Misconfiguration
     */
    public function getCmsBlock()
    {
        $helper = Mage::helper('paybyfinance');
        switch ($this->_type) {
            case 'accepted':
                $id = Mage::getStoreConfig($helper::XML_PATH_BLOCK_ACCEPTED);
                break;
            case 'referred':
                $id = Mage::getStoreConfig($helper::XML_PATH_BLOCK_REFERRED);
                break;
            case 'declined':
                $id = Mage::getStoreConfig($helper::XML_PATH_BLOCK_DECLINED);
                break;
            case 'abandoned':
                $id = Mage::getStoreConfig($helper::XML_PATH_BLOCK_ABANDONED);
                break;
            case 'error':
                $id = Mage::getStoreConfig($helper::XML_PATH_BLOCK_ERROR);
                break;
            default:
                throw new Exception("Undefined status block type.", 1);
        }

        $block = Mage::getModel('cms/block')->load($id);
        $this->_order = Mage::getModel('sales/order')->load(
            Mage::getSingleton('paybyfinance/session')->getData('order_id')
        );
        $helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();
        $processor->setVariables(
            array(
                'order_id' => $this->getOrderIdText(),
                'phone' => Mage::getStoreConfig('general/store_information/phone')
            )
        );
        $html = $processor->filter($block->getContent());

        return $html;
    }

    /**
     * Get order ID text based on the circumstances
     *
     * @return string Link or the order increment id
     */
    private function getOrderIdText()
    {
        if ($this->canPrint()) {
            $url = $this->getUrl(
                'sales/order/view/',
                array(
                    'order_id' => $this->_order->getId(),
                    '_secure' => true
                )
            );

            return $this->__('<a href="%s">%s</a>', $url, $this->_order->getIncrementId());
        }

        return $this->_order->getIncrementId();
    }

    /**
     * Whatever we can print the order link or not
     *
     * @return bool Decision
     */
    private function canPrint()
    {
        if (!$this->_order) {
            return false;
        }
        if (in_array(
            $this->_order->getState(),
            Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates()
        )) {
            return false;
        }

        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

}
