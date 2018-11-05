<?php
/**
 * PAYONE Prestashop Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PAYONE Prestashop Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PAYONE Prestashop Connector. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @author    patworx multimedia GmbH <service@patworx.de>
 * @copyright 2003 - 2018 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone;

$sFcPayoneBaseDir = dirname(__FILE__) . '../../../';
require_once($sFcPayoneBaseDir . 'config/config.inc.php');
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once($sFcPayoneBaseDir . 'init.php');
require_once(dirname(__FILE__) . '/fcpayone.php');

class Status
{

    /**
     * Status constructor.
     *
     * Execute security checks
     */
    public function __construct()
    {
        $this->isIPValid();
        $this->isKeyValid();
    }

    /**
     * Executes
     * - transaction saving
     * - order state handling
     * - status forwarding
     */
    public function process()
    {
        echo 'TSOK';
        $this->saveTransaction();
        $this->handleOrderState();
        $this->handleForwarding();
        exit;
    }

    /**
     * Check if remote ip is valid
     * only payone ips are allowed
     * whitelist set in \Payone\Validation\Validation
     */
    private function isIPValid()
    {
        $sRemoteIp = \Tools::getRemoteAddr();
        $oValidation = new \Payone\Validation\Validation();
        if (!$oValidation->isValidPayoneIp($sRemoteIp)) {
            \Payone\Base\Registry::getLog()->log('transaction status update failed: ip not valid - ' . $sRemoteIp, 4);
            echo 'IP INVALID';
            exit;
        }
    }

    /**
     * Returns request data
     * remove not needed params
     *
     * @return array
     */
    private function getRequestData()
    {
        $aRequest = \Tools::getAllValues();
        $aRemoveFromRequest = array('isolang', 'id_lang', 'module', 'controller', 'fc');
        foreach ($aRemoveFromRequest as $sParam) {
            unset($aRequest[$sParam]);
        }
        return $aRequest;
    }

    /**
     * Save transaction data
     */
    private function saveTransaction()
    {
        $oTransaction = new \Payone\Base\Transaction();
        $oTransaction->setData($this->getRequestData());
        $oTransaction->save();
    }

    /**
     * Handles order state update
     * and updates transaction
     */
    private function handleOrderState()
    {
        $oPayoneOrder = new \Payone\Base\Order();
        $aOrderData = $oPayoneOrder->getOrderDataByTxId($this->getTxId());
        if ($aOrderData) {
            $this->updateOrderState($aOrderData);
            $oTransaction = new \Payone\Base\Transaction();
            $oTransaction->updateOrderId($this->getTxId(), (int)$aOrderData['id_order']);
            $oTransaction->updateReference($this->getTxId(), $aOrderData['reference']);
        }
    }

    /**
     * Updates order state
     *
     * @param $aOrderData
     */
    private function updateOrderState($aOrderData)
    {
        $iOrderState = $this->getOrderStateId($aOrderData);
        if ($iOrderState && $iOrderState > 0) {
            $oOrderHistory = new \OrderHistory();
            $oOrderHistory->id_order = (int)$aOrderData['id_order'];
            $oOrderHistory->changeIdOrderState($iOrderState, $oOrderHistory->id_order);
            $oOrderHistory->addWithemail();
            $oOrderHistory->save();
        }
    }

    /**
     * Returns order state based on transaction configuration
     *
     * @param $aOrderData
     * @return mixed
     */
    private function getOrderStateId($aOrderData)
    {
        $aStates = \Payone\Base\Transaction::getStates();
        $sAction = $this->getAction();
        $sOrderStateIdent = null;
        foreach ($aStates as $sState) {
            if (\Tools::strtolower($sAction) == \Tools::strtolower($sState)) {
                $sOrderStateIdent = 'FC_PAYONE_PAYMENT_TRANSACTION_MAPPING_'
                    . \Tools::strtoupper($aOrderData['paymentid']) . '_'
                    . \Tools::strtoupper($sState);
                break;
            }
        }
        $iOrderState = \Configuration::get($sOrderStateIdent);
        if ($iOrderState) {
            return $iOrderState;
        }
    }

    /**
     * Returns txid
     *
     * @return mixed
     */
    private function getTxId()
    {
        return \Tools::getValue('txid');
    }

    /**
     * Check if key is valid
     *
     * @return bool
     */
    private function isKeyValid()
    {
        $sKey = \Tools::getValue('key');
        $sConfigurationKey = \Configuration::get('FC_PAYONE_CONNECTION_PORTALKEY');
        if ($sKey != md5($sConfigurationKey)) {
            echo 'Key wrong or missing!';
            \Payone\Base\Registry::getLog()->log('transaction status update failed: key wrong or missing', 4);
            exit;
        }
    }

    /**
     * Returns transaction action
     *
     * @return mixed
     */
    private function getAction()
    {
        return \Tools::getValue('txaction');
    }

    /**
     * Returns forwarding configuration for given action
     *
     * @param string $sAction
     *
     * @return string
     */
    private function getForwardingConfiguration($sAction)
    {
        return trim(\Configuration::get('FC_PAYONE_TRANSACTION_FORWARDING_' . \Tools::strtoupper($sAction)));
    }

    /**
     * Returns array with forwardings
     *
     * @return array
     */
    private function getForwardingList()
    {
        $sAction = $this->getAction();
        if (!($sForwardingConfig = $this->getForwardingConfiguration($sAction))) {
            return array();
        }

        $aForwardings = array();
        $aConfig = explode("\n", str_replace("\r", '', $sForwardingConfig));
        foreach ($aConfig as $sForwarding) {
            $aForwarding = $this->getForwardingConfigFromString($sForwarding);
            if (count($aForwarding) > 0) {
                $aForwardings[] = $aForwarding;
            }
        }
        return $aForwardings;
    }

    /**
     * Returns forwarding config array for config line
     *
     * @param $sForwarding
     * @return array
     */
    private function getForwardingConfigFromString($sForwarding)
    {
        $aForwarding = explode(';', $sForwarding);
        if (count($aForwarding) > 0 && isset($aForwarding[0])) {
            if (isset($aForwarding[1])) {
                $iTimeout = (int)trim($aForwarding[1]);
            } else {
                $iTimeout = 20;
            }

            return array(
                'url' => trim($aForwarding[0]),
                'timeout' => $iTimeout,
            );
        }
    }

    /**
     * Handles request forwarding
     */
    private function handleForwarding()
    {
        $aForwardings = $this->getForwardingList();
        if (count($aForwardings) > 0) {
            foreach ($aForwardings as $aForwarding) {
                $oDispatcher = new \Payone\Request\Dispatcher();
                $oDispatcher->setCertificationUsage(false);
                $oDispatcher->setTimeout($aForwarding['timeout']);
                $oDispatcher->dispatchRequest(\Tools::getAllValues(), false, false, $aForwarding['url']);
            }
        }
    }
}

$oScript = new \Payone\Status();
$oScript->process();
