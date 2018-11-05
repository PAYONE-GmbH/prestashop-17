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

namespace Payone\Request\Builder\Order;

use Payone\Base\Registry;

class Base extends \Payone\Request\Builder\Base
{
    /**
     * Order data
     * @var array
     */
    protected $aOrderData = null;

    /**
     * Capture amount
     *
     * @var float
     */
    protected $dAmount = null;

    /**
     * Description
     *
     * @var string
     */
    protected $sDescription = null;

    /**
     * Sets firtst order request
     * can contain basket...
     *
     * @var null
     */
    protected $aFirstRequest = null;

    /**
     * Sets order data
     *
     * @param $aOrderData
     */
    public function setOrderData($aOrderData)
    {
        $this->aOrderData = $aOrderData;
    }

    /**
     * Returns order data
     *
     * @return array
     */
    protected function getOrderData()
    {
        return $this->aOrderData;
    }

    /**
     * Sets capture amount
     *
     * @param float $dAmount
     */
    public function setAmount($dAmount)
    {
        $this->dAmount = $dAmount;
    }

    /**
     * Returns capture amount
     *
     * @return float
     */
    protected function getAmount()
    {
        return $this->dAmount;
    }

    /**
     * Sets first order request
     *
     * @param array $aRequest
     */
    public function setFirstRequest($aRequest) {
        $this->aFirstRequest = $aRequest;
    }

    /**
     * Returns first order request
     *
     * @return array|null
     */
    protected function getFirstRequest() {
        return $this->aFirstRequest;
    }

    /**
     * Sets user params
     */
    public function build()
    {
        parent::build();
        if (is_array($this->getOrderData())) {
            $this->setAuthToRequest();
            $this->setOrderDataToRequest();
            $this->setSequenceToRequest();
        }
    }

    /**
     * Sets auth to request
     */
    protected function setAuthToRequest()
    {
        $oRequestBuilder = new \Payone\Request\Builder\Auth;
        $oRequestBuilder->build();
        $aParams = $oRequestBuilder->getParams();
        if (is_array($aParams) && count($aParams) > 0) {
            foreach ($aParams as $sKey => $sValue) {
                $this->setParam($sKey, $sValue);
            }
        }
    }

    /**
     * Sets order data to request
     */
    protected function setOrderDataToRequest()
    {
        $aOrderData = $this->getOrderData();
        $oOrder = Registry::getHelperPrestashop()->fcPayoneGetOrder($aOrderData['id_order']);
        if (\Validate::isLoadedObject($oOrder)) {
            $oCurrency = Registry::getHelperPrestashop()->fcPayoneGetCurrency($oOrder->id_currency);
            $this->setParam('currency', $oCurrency->iso_code);
            $this->setParam('amount', Registry::getHelper()->getConvertedAmount($this->getAmount()));

            $oLanguage = Registry::getHelperPrestashop()->fcPayoneGetLanguage($oOrder->id_lang);
            $this->setParam('language', $oLanguage->iso_code);

            $this->setParam('txid', $aOrderData['txid']);
            $this->setParam('mode', $aOrderData['mode']);
            return true;
        }
    }

    /**
     * Sets sequence number to request
     */
    protected function setSequenceToRequest()
    {
        $aOrderData = $this->getOrderData();
        $sTable = _DB_PREFIX_ . \Payone\Base\Transaction::getTable();
        $iTxId = (int)\pSQL($aOrderData['txid']);
        $sQ = "select max(sequencenumber) from {$sTable} where txid = {$iTxId}";
        $iSequenceNumber = (int)\Db::getInstance()->getValue($sQ);
        $iSequenceNumber += 1;
        $this->setParam('sequencenumber', $iSequenceNumber);
    }


    /**
     * Set items from first request to new reuest
     * cant use real basket, because of possible changes
     */
    protected function setItemsFromFirstRequestToRequest() {
        $aFirstRequest = $this->getFirstRequest();
        if( is_array($aFirstRequest) && count($aFirstRequest) > 0 ) {
            foreach( $aFirstRequest as $sKey => $sValue ) {
                if ( preg_match('/(it|id|pr|no|de|va)(\[\d\])/', $sKey) ) {
                    $this->setParam($sKey, $sValue);
                }
            }
        }
    }
}
