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
 * @copyright 2003 - 2020 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone\Request;

use Payone\Base\Registry;
use Payone\Validation\Validation;

class Request
{

    /**
     * Request response
     *
     * @var array
     */
    protected $aResponse = array();

    /**
     * Request
     *
     * @var array
     */
    protected $aRequest = array();

    /**
     * Request table
     *
     * @var string
     */
    protected static $sTable = 'fcpayonerequests';

    /**
     * Additional data for save
     * sometimes not all needed save params are
     * set in request, so add them here
     * @var null
     */
    protected $aAdditionalSaveData = null;

    /**
     * Special reference in case it
     * cant be added to request
     *
     * @var string
     */
    protected $sReference = null;

    /**
     * Sets request response
     *
     * @param array $aResponse
     */
    protected function setResponse($aResponse)
    {
        $this->aResponse = $aResponse;
    }

    /**
     * Returns response
     * @param boolean $blRaw
     *
     * @return array $aResponse
     */
    public function getResponse($blRaw = false)
    {
        if ($blRaw) {
            return $this->aResponse;
        }
        return $this->normaliseResponse($this->aResponse);
    }

    /**
     * Sets request
     *
     * @param array $aRequest
     */
    protected function setRequest($aRequest)
    {
        $this->aRequest = $aRequest;
    }

    /**
     * Returns response
     *
     * @return array $aResponse
     */
    protected function getRequest()
    {
        return $this->aRequest;
    }

    /**
     * Sets additonal save data
     *
     * @param $sParam
     * @param $sValue
     */
    public function setAdditionalSaveData($sParam, $sValue)
    {
        $this->aAdditionalSaveData[$sParam] = $sValue;
    }

    /**
     * Returns additonal save data
     *
     * @return array
     */
    protected function getAdditionalSaveData()
    {
        return $this->aAdditionalSaveData;
    }

    /**
     * Returns specific param from additional save data
     *
     * @param $sParam
     * @return mixed
     */
    protected function getParamFromAdditionalSaveData($sParam)
    {
        $aData = $this->getAdditionalSaveData();
        if (isset($aData[$sParam])) {
            return $aData[$sParam];
        }
    }

    /**
     * Sends request and sets response
     * @param array $aRequest
     * @return boolean
     */
    public function sendRequest($aRequest = null)
    {
        if (!$aRequest && $this->getRequest()) {
            $aRequest = $this->getRequest();
        }
        if (!$aRequest) {
            return false;
        }
        $oRequestDispatcher = new Dispatcher();
        $oValidation = new Validation();
        if (!$oValidation->validateRequest($aRequest)) {
            return false;
        }

        $blUseFileGetContents = false;
        if ($aRequest['request'] == 'getfile') {
            $blUseFileGetContents = true;
        }

        $aResult = $oRequestDispatcher->dispatchRequest($aRequest, false, $blUseFileGetContents);
        $this->setResponse($aResult['response']);

        return $this->save();
    }

    /**
     * Normalise response from payone
     *
     * @param array $aResponse
     * @return array
     */
    protected function normaliseResponse($aResponse)
    {
        $aNormalisedResponse = array();
        if (!is_array($aResponse)) {
            return null;
        }

        foreach ($aResponse as $iLineNum => $sLine) {
            $iPos = strpos($sLine, "=");
            if ($iPos > 0) {
                $aNormalisedResponse[\Tools::substr($sLine, 0, $iPos)] = trim(\Tools::substr($sLine, $iPos + 1));
            } elseif (\Tools::strlen($sLine) > 0) {
                $aNormalisedResponse[$iLineNum] = $sLine;
            }
        }

        return $aNormalisedResponse;
    }

    /**
     * Request table
     *
     * @return string
     */
    public static function getTable()
    {
        return self::$sTable;
    }

    /**
     * saves request to db
     *
     * @return boolean
     */
    public function save()
    {
        $aRequest = Registry::getHelper()->cleanData($this->getRequest());
        $aResponse = Registry::getHelper()->cleanData($this->getResponse());

        $aData = array();
        if (isset($aRequest['reference'])) {
            $aData['reference'] = \pSQL($aRequest['reference']);
        }

        $aData['request'] = \pSQL(\Tools::jsonEncode($aRequest));
        //if there is an status in the getfile response, something went wrong with
        // request
        if ($aRequest['request'] == 'getfile' && (!isset($aResponse['status']))) {
            $aData['response'] = base64_encode($this->getResponse(true));
        } else {
            $aData['response'] =\pSQL(\Tools::jsonEncode($aResponse));
        }

        $aData['status'] = \pSQL($aResponse['status']);

        if (($iTxId = $this->getParamFromRequest('txid', $aRequest, $aResponse))) {
            $aData['txid'] = \pSQL($iTxId);
        }

        if (($iUserId = $this->getParamFromRequest('userid', $aRequest, $aResponse))) {
            $aData['userid'] = \pSQL($iUserId);
        }

        $aAdditionalSaveData = $this->getAdditionalSaveData();
        if (isset($aAdditionalSaveData) && count($aAdditionalSaveData) > 0) {
            foreach ($aAdditionalSaveData as $sParam => $sValue) {
                $aData[$sParam] = \pSQL($sValue);
            }
        }

        $aData['date'] = date('Y-m-d H:i:s');

        return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
    }

    /**
     * Returns first request for txid
     *
     * @param $iRawTxId
     * @return array
     */
    protected function getFirstRequestByTxid($iRawTxId)
    {
        if ($iRawTxId) {
            $iTxId = (int)\pSQL($iRawTxId);
            $sTable = _DB_PREFIX_ . self::getTable();
            $sQ = "select request from " . $sTable .
                " where txid = '{$iTxId}' and (status = 'APPROVED' || status = 'REDIRECT') order by date asc";
            $aRow = \Db::getInstance()->getRow($sQ);
            if (isset($aRow['request'])) {
                $oRequest = \Tools::jsonDecode($aRow['request'], true);
                return $oRequest;
            }
        }
    }

    /**
     * Returns param from response/request
     * trys to get txid from response if not found, check
     * the request array
     * @param string $sParam
     * @param null $aRequest
     * @param null $aResponse
     * @return mixed
     */
    protected function getParamFromRequest($sParam, $aRequest = null, $aResponse = null)
    {
        if (!$aResponse) {
            $aResponse = Registry::getHelper()->cleanData($this->getResponse());
        }

        if (!$aRequest) {
            $aRequest = Registry::getHelper()->cleanData($this->getRequest());
        }

        if (isset($aResponse[$sParam])) {
            return $aResponse[$sParam];
        } elseif (isset($aRequest[$sParam])) {
            return $aRequest[$sParam];
        }
    }


    /**
     * Process payment request
     *
     * @param object $oContext
     * @param object $oSelectedPayment
     * @param array $aForm
     *
     * @return boolean
     */
    public function processPayment($oContext, $oSelectedPayment, $aForm = null)
    {
        try {
            $this->buildPaymentRequest($oContext, $oSelectedPayment, $aForm);
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

    /**
     * Triggers request building
     *
     * @param object $oContext
     * @param object $oSelectedPayment
     * @param array $aForm
     */
    protected function buildPaymentRequest($oContext, $oSelectedPayment, $aForm = null)
    {
        $sBuilderClass = $oSelectedPayment->getRequestBuilderClass();

        if (!class_exists($sBuilderClass) &&
            ($oParentPayment = Registry::getPayment()->getParentPaymentMethod($oSelectedPayment->getParentId()))
        ) {
            $sBuilderClass = $oParentPayment->getRequestBuilderClass();
        }

        if (class_exists($sBuilderClass) && ($oBuilder = new $sBuilderClass)) {
            $oBuilder->setCart($oContext->cart);
            $oBuilder->setPayment($oSelectedPayment);
            $oBuilder->setForm($aForm);
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        }
    }


    /**
     * Process mandate manage request
     *
     * @param object $oContext
     * @param object $oSelectedPayment
     * @param array $aForm
     *
     * @return boolean
     */
    public function processMandateManage($oContext, $oSelectedPayment, $aForm = null)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Mandate;
            $oBuilder->setCart($oContext->cart);
            $oBuilder->setPayment($oSelectedPayment);
            $oBuilder->setForm($aForm);
            $oBuilder->setMandateRequestType('managemandate');
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

    /**
     * Process mandate getfile request
     *
     * @param string $sFileReference
     * @param string $sMode
     *
     * @return boolean
     */
    public function processMandateGetFile($sFileReference, $sMode)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Mandate;
            $oBuilder->setFileReference($sFileReference);
            $oBuilder->setMode($sMode);
            $oBuilder->setMandateRequestType('getfile');
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

    /**
     * Process paypal express generic request
     *
     * @param object $oContext
     * @param object $oSelectedPayment
     * @param string $sWorkOrderId
     *
     * @return boolean
     */
    public function processPayPalExpressCheckout($oContext, $oSelectedPayment, $sWorkOrderId = null)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Payment\PayPalExpress;
            $oBuilder->setCart($oContext->cart);
            $oBuilder->setPayment($oSelectedPayment);
            $oBuilder->setGenericRequest(true);
            $oBuilder->setWorkOrderId($sWorkOrderId);
            $oBuilder->setAction('expresscheckout');
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }

        return $this->sendRequest($this->getRequest());
    }

    /**
     * Process paypal express generic request
     *
     * @param object $oContext
     * @param object $oSelectedPayment
     * @param string $sWorkOrderId
     *
     * @return boolean
     */
    public function processPayPalExpressExecution($oContext, $oSelectedPayment, $sWorkOrderId = null)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Payment\PayPalExpress;
            $oBuilder->setCart($oContext->cart);
            $oBuilder->setPayment($oSelectedPayment);
            $oBuilder->setWorkOrderId($sWorkOrderId);
            $oBuilder->setAction('execution');
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

    /**
     * Bulds and sends capture request
     *
     * @param $oPayment
     * @param $aOrderData
     * @param $dAmount
     * @param $blSettleAccount
     * @return boolean
     */
    public function processCapture($oPayment, $aOrderData, $dAmount, $blSettleAccount = true)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Order\Capture;
            $oBuilder->setOrderdata($aOrderData);
            if ( ($aFirstRequest = $this->getFirstRequestByTxid($aOrderData['txid'])) ){
                $oBuilder->setFirstRequest($aFirstRequest);
            }
            $oBuilder->setPayment($oPayment);
            $oBuilder->setAmount($dAmount);
            $oBuilder->setAccountSettlement($blSettleAccount);
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

    /**
     * Bulds and sends capture request
     *
     * @param $oPayment
     * @param $aOrderData
     * @param $dAmount
     * @param $aBankData
     *
     * @return boolean
     */
    public function processDebit($oPayment, $aOrderData, $dAmount, $aBankData = null)
    {
        try {
            $oBuilder = new \Payone\Request\Builder\Order\Debit;
            $oBuilder->setOrderdata($aOrderData);
            if ( ($aFirstRequest = $this->getFirstRequestByTxid($aOrderData['txid'])) ){
                $oBuilder->setFirstRequest($aFirstRequest);
            }
            $oBuilder->setPayment($oPayment);
            $oBuilder->setAmount($dAmount);
            $oBuilder->setBankData($aBankData);
            $oBuilder->build();
            $this->setRequest($oBuilder->getParams());
        } catch (\Exception $oEx) {
            Registry::getErrorHandler()->setError('request', $oEx->getMessage());
        }
        return $this->sendRequest($this->getRequest());
    }

}
