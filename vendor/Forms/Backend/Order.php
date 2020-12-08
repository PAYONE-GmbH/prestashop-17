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

namespace Payone\Forms\Backend;

use Payone\Base\Registry;
use Payone\Request\Request;
use Payone\Response\Response;

class Order extends Base
{

    /**
     * Returns form
     *
     * @return mixed
     */
    public function getForm()
    {
        $oOrder = $this->getOrder();
        $oPayoneOrder = new \Payone\Base\Order();
        $aOrderData = $oPayoneOrder->getOrderData($oOrder->id);

        $this->getModule()->fcPayoneAddDefaultTemplateVars();
        $oPayment = Registry::getPayment()->getPaymentMethod($aOrderData['paymentid']);
        if ($oPayment) {
            $this->getSmarty()->assign('oFcPayonePayment', $oPayment);
        }
        $this->addTransactionList($oOrder->id);
        $this->addActionForms($oOrder, $aOrderData, $oPayment);
        $this->getSmarty()->assign('isPrestaShop1770rHigher', Registry::getHelperPrestaShop()->isPrestaShop1770rHigher());
        $this->addLastRequest($aOrderData['txid']);
        $this->addLastRequestWithClearingData($aOrderData['txid']);
        if ($aOrderData['paymentid'] == 'debit') {
            $oUser = new \Payone\Base\User();
            $iCustomerId = $oUser->getCustomerIdByPayoneUserId($aOrderData['userid']);
            $this->getModule()->fcPayoneAddMandateDownloadLink($aOrderData['id_order'], $iCustomerId);
        }


        //need to be called at the end
        $this->displayErrors();
        return $this->getModule()->display(
            Registry::getHelper()->getModulePath(),
            'views/templates/hook/admin/adminorder.tpl'
        );
    }

    /**
     * Add last request with clearing- / bankdata to template
     *
     * @param $iTxId
     */
    public function addLastRequestWithClearingData($iTxId)
    {
        $sTable = _DB_PREFIX_ . Request::getTable();
        $iTxId = (int)\pSQL($iTxId);
        $sQ = "select * from $sTable where txid = '{$iTxId}' and `response` like '%clearing_%' order by date desc";
        $aLastRequest = \Db::getInstance()->getRow($sQ);
        if ($aLastRequest) {
            $this->getSmarty()->assign('aFcPayoneLastRequestWithClearingData', \Tools::jsonDecode($aLastRequest['response'], true));
            return true;
        }
        return;
    }


    /**
     * Adds order actions forms
     * to template
     *
     * @param $oOrder
     * @param $aOrderData
     * @param $oPayment
     */
    protected function addActionForms($oOrder, $aOrderData, $oPayment)
    {
        if (is_array($aOrderData) && count($aOrderData) > 0) {
            $this->getSmarty()->assign('aFcPayoneOrderData', $aOrderData);
            if ($this->isCaptureAllowed($aOrderData)) {
                $this->handleCaptureForm($oOrder, $aOrderData, $oPayment);
            }
            if ($this->isDebitAllowed($aOrderData)) {
                $this->handleDebitForm($oOrder, $aOrderData);
            }
        } else {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_NO_DATA_FOUND');
        }
    }

    /**
     * Ads transactionlist to template
     *
     * @param $iOrderId
     */
    protected function addTransactionList($iOrderId)
    {
        $oTransaction = new \Payone\Base\Transaction();
        $aTransactions = $oTransaction->getAdminTransactions($iOrderId, 'id_order');
        if (is_array($aTransactions) && count($aTransactions) > 0) {
            $this->getSmarty()->assign('aFcPayoneTransactions', $aTransactions);
        }

        $aFirstTransaction = $oTransaction->getFirstTransaction($iOrderId, 'id_order');
        if (is_array($aFirstTransaction) && count($aFirstTransaction)) {
            $this->getSmarty()->assign('aFcPayoneFirstTransaction', $aFirstTransaction);
        }
    }

    /**
     * Add last request to template
     *
     * @param $iTxId
     */
    protected function addLastRequest($iTxId)
    {
        $sTable = _DB_PREFIX_ . Request::getTable();
        $iTxId = (int)\pSQL($iTxId);
        $sQ = "select * from $sTable where txid = '{$iTxId}' order by date desc";
        $aLastRequest = \Db::getInstance()->getRow($sQ);
        if ($aLastRequest) {
            $this->getSmarty()->assign('aFcPayoneLastRequest', $aLastRequest);
        }
    }

    /**
     * Outputs errors
     */
    protected function displayErrors()
    {
        $aErrors = Registry::getErrorHandler()->getErrors();
        if (is_array($aErrors) && count($aErrors) > 0) {
            $this->getSmarty()->assign('aFcPayoneErrors', $aErrors);
        }
        Registry::getErrorHandler()->deleteErrors();
    }

    /**
     * Returns true if capture is allowed
     *
     * @param $aOrderData
     * @return bool
     */
    protected function isCaptureAllowed($aOrderData)
    {
        if ($aOrderData['requesttype'] == 'authorization') {
            return false;
        }

        $iTxId = (int)\pSQL($aOrderData['txid']);
        $sQ = "select count(*) from " . _DB_PREFIX_ . \Payone\Base\Transaction::getTable() . " where txid = '{$iTxId}'";
        return (bool)\Db::getInstance()->getValue($sQ);
    }

    /**
     * Returns true if debit request is allowed
     *
     * @param $aOrderData
     * @return boolean
     */
    protected function isDebitAllowed($aOrderData)
    {
        if ($aOrderData['requesttype'] == 'authorization') {
            return true;
        }

        $iTxId = (int)\pSQL($aOrderData['txid']);
        $sQ = "select count(*) from " . _DB_PREFIX_ . \Payone\Base\Transaction::getTable() .
            " where txid = '{$iTxId}' and txaction = 'capture'";
        return (bool)\Db::getInstance()->getValue($sQ);
    }

    /**
     * Returns capture form html
     *
     * @param $oOrder
     * @param $aOrderData
     * @param $oPayment
     * @return mixed
     */
    protected function handleCaptureForm($oOrder, $aOrderData, $oPayment)
    {
        if (\Tools::isSubmit('submitPayoneCapture')) {
            $this->processCaptureSubmit($oOrder, $aOrderData);
        }

        $this->addBaseParamsToSubTemplate($oOrder, $aOrderData);

        $blSettleAccount = false;
        if ($oPayment && $oPayment->isAccountSettlementAllowed()) {
            $blSettleAccount = true;
        }
        $this->getSmarty()->assign('blFcPayoneAccountSettlement', $blSettleAccount);
        $this->checkForFixedAmount($oPayment, $aOrderData);

        $sForm = $this->getSmarty()->fetch(
            Registry::getHelper()->getModulePath() . 'views/templates/hook/admin/inc/capture.tpl'
        );
        $this->getSmarty()->assign('sFcPayoneCaptureForm', $sForm);
    }

    /**
     * Check for fixed amount capture
     * disables input field and sets amount
     *
     * @param $oPayment
     * @param $aOrderData
     */
    protected function checkForFixedAmount($oPayment, $aOrderData) {
        $blDisableAmountInput = false;
        $dFcPayoneFixedAmount = false;
        if ($oPayment && $oPayment->isAmountInputDisabled()) {
            $blDisableAmountInput = true;
            $oTransaction = new \Payone\Base\Transaction();
            $aTransaction = $oTransaction->getFirstTransaction($aOrderData['id_order'], 'id_order');
            if (is_array($aTransaction) && count($aTransaction) > 0) {
                $dFcPayoneFixedAmount = (double)$aTransaction['data']['price'];
            }
        }
        $this->getSmarty()->assign('blFcPayoneDisableAmountInput', $blDisableAmountInput);
        $this->getSmarty()->assign('dFcPayoneFixedAmount', $dFcPayoneFixedAmount);
    }

    /**
     * Process capture submit
     *
     * @param $oOrder
     * @param $aOrderData
     */
    protected function processCaptureSubmit($oOrder, $aOrderData)
    {
        $dAmount = $this->getActionAmount();
        if (!$dAmount || $dAmount < 0 || $dAmount > $oOrder->total_paid) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_ACTION_AMOUNT_NOT_VALID', true);
        } else {
            $oPayment = Registry::getPayment()->getPaymentMethod($aOrderData['paymentid']);
            $oRequest = new Request();
            $oRequest->setAdditionalSaveData('reference', $aOrderData['reference']);
            $oRequest->setAdditionalSaveData('userid', $aOrderData['userid']);

            $blSettleAccount = true;
            if (\Tools::isSubmit('payone_settleaccount')) {
                $blSettleAccount = (bool)\Tools::getValue('payone_settleaccount');
            }
            if (($oRequest->processCapture($oPayment, $aOrderData, $dAmount, $blSettleAccount))) {
                $oResponse = new Response();
                $oResponse->setResponse($oRequest->getResponse());
                $oResponse->processCapture();
            }
        }
    }


    /**
     * Adds base params to sub templte
     *
     * @param $oOrder
     * @param $aOrderData
     */
    protected function addBaseParamsToSubTemplate($oOrder, $aOrderData)
    {
        $this->getContext()->controller->addJS(Registry::getHelper()->getModulePath()
            . 'views/js/admin/fcpayoneorder.js', 'all');

        $oTransaction = new \Payone\Base\Transaction();
        $aFirstTransactionData = $oTransaction->getFirstTransaction($aOrderData['id_order'], 'id_order');
        if (isset($aFirstTransactionData)) {
            $this->getSmarty()->assign('aFcPayoneFirstTransactionData', $aFirstTransactionData);
        }

        $this->getSmarty()->assign('aFcPayoneOrderData', $aOrderData);
        if (isset($oOrder->id_currency)) {
            $oCurrency = Registry::getHelperPrestashop()->fcPayoneGetCurrency($oOrder->id_currency);
            $this->getSmarty()->assign('sFcPayoneCurrencyIso', $oCurrency->iso_code);
        }
    }

    /**
     * Returns debit form html
     *
     * @param $oOrder
     * @param $aOrderData
     *
     * @return mixed
     */
    protected function handleDebitForm($oOrder, $aOrderData)
    {
        if (\Tools::isSubmit('submitPayoneDebit')) {
            $this->processDebitSubmit($aOrderData);
        }

        $this->addBaseParamsToSubTemplate($oOrder, $aOrderData);
        $oPayment = Registry::getPayment()->getPaymentMethod($aOrderData['paymentid']);
        $blBankDataNeeded = false;
        if ($oPayment && $oPayment->isBankDataNeededForDebit()) {
            $blBankDataNeeded = true;
        }
        $this->getSmarty()->assign('blFcPayoneBankDataNeeded', $blBankDataNeeded);
        $this->checkForFixedAmount($oPayment, $aOrderData);
        $sForm = $this->getSmarty()->fetch(
            Registry::getHelper()->getModulePath() . 'views/templates/hook/admin/inc/debit.tpl'
        );
        $this->getSmarty()->assign('sFcPayoneDebitForm', $sForm);
    }

    /**
     * Process debit submit
     *
     * @param $aOrderData
     */
    protected function processDebitSubmit($aOrderData)
    {
        $dAmount = $this->getActionAmount();
        if (!$dAmount) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_ACTION_AMOUNT_NOT_VALID', true);
        } else {
            // amount for credit entry has to be negative
            if ((double)$dAmount > 0) {
                $dAmount = (double)$dAmount * -1;
            }
            if ($dAmount && $dAmount < 0) {
                $oPayment = Registry::getPayment()->getPaymentMethod($aOrderData['paymentid']);
                $oRequest = new Request();
                $oRequest->setAdditionalSaveData('reference', $aOrderData['reference']);
                $oRequest->setAdditionalSaveData('userid', $aOrderData['userid']);
                $aBankData = $this->getDebitBankData();
                if (($oRequest->processDebit($oPayment, $aOrderData, $dAmount, $aBankData))) {
                    $oResponse = new Response();
                    $oResponse->setResponse($oRequest->getResponse());
                    $oResponse->processDebit();
                }
            }
        }
    }

    /**
     * Returns array with debit bank data
     *
     * @return array
     */
    protected function getDebitBankData()
    {
        return array(
            'bankcountry' => \Tools::getValue('payone_bankcountry'),
            'bankaccount' => \Tools::getValue('payone_bankaccount'),
            'bankcode' => \Tools::getValue('payone_bankcode'),
            'bankaccountholder' => \Tools::getValue('payone_bankaccountholder'),
        );
    }

    /**
     * Returns submitted amount
     *
     * @return mixed
     */
    protected function getActionAmount()
    {
        return str_replace(',', '.', \Tools::getValue('payone_amount'));
    }
}
