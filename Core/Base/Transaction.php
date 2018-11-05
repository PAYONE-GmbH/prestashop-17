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

namespace Payone\Base;

class Transaction
{

    /**
     * Payone transaction states
     *
     * @var array
     */
    protected static $aStates = array(
        'appointed',
        'capture',
        'paid',
        'underpaid',
        'cancelation',
        'refund',
        'debit',
        'reminder',
        #'vauthorization',
        #'vsettlement',
        'transfer',
        'invoice',
    );

    /**
     * Transaction database table
     *
     * @var string
     */
    protected static $sTable = 'fcpayonetransactions';

    /**
     * Payone transaction data
     *
     * @var null
     */
    protected $aData = null;

    /**
     * Mapping with txaction to lang ident
     * needs to have entry for 0 and 1, because of that
     * some are duplicated
     *
     * @var array
     */
    protected $aActionLangMapping = array(
        'receivable' => array(
            'capture' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_CAPTURE',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_CAPTURE'
            ),
            'cancelation' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_CANCELATION',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_CANCELATION'
            ),
            'appointed' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_APPOINTED_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_APPOINTED_2'
            ),
            'reminder' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_REMINDER',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_REMINDER'
            ),
            'debit' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_DEBIT_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_REC_DEBIT_2'
            )
        ),
        'payment' => array(
            'capture' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_CAPTURE_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_CAPTURE_2',
            ),
            'cancelation' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_CANCELATION_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_CANCELATION_2'
            ),
            'paid' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_PAID_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_PAID_2'
            ),
            'underpaid' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_UNDERPAID_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_UNDERPAID_2'
            ),
            'debit' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_DEBIT_1',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_DEBIT_2'
            ),
            'transfer' => array(
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_TRANSFER',
                'FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION_PAY_TRANSFER'
            )
        )
    );

    /**
     * Returns database table name
     *
     * @return string
     */
    public static function getTable()
    {
        return self::$sTable;
    }

    /**
     * Returns transaction data
     *
     * @return array $aData
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * Transaction data setter
     * @param array $aData
     */
    public function setData($aData)
    {
        $this->aData = $aData;
    }

    /**
     * Returns array with lang mapping
     *
     * @return array
     */
    protected function getActionLangMapping()
    {
        return $this->aActionLangMapping;
    }


    /**
     * saves request to db
     *
     * @return boolean
     */
    public function save()
    {
        if (($aRawData = $this->getData())) {
            $aTransactionData = array();
            foreach ($aRawData as $sKey => $sValue) {
                $aTransactionData[$sKey] = \Payone\Base\Registry::getHelper()->convertToUTF8($sValue);
            }

            $oUser = new \Payone\Base\User;
            $iCustomerId = $oUser->getCustomerIdByPayoneUserId($aTransactionData['userid']);
            $aData = array();
            $aData['id_customer'] = \pSQL($iCustomerId);
            $aData['userid'] = \pSQL($aTransactionData['userid']);
            $aData['txid'] = \pSQL($aTransactionData['txid']);
            $aData['txaction'] = \pSQL($aTransactionData['txaction']);
            if (isset($aTransactionData['reference'])) {
                $aData['reference'] = \pSQL($aTransactionData['reference']);
            }

            if (isset($aTransactionData['sequencenumber'])) {
                $aData['sequencenumber'] = \pSQL($aTransactionData['sequencenumber']);
            }

            $aData['data'] = \pSQL(\Tools::jsonEncode(\Payone\Base\Registry::getHelper()->cleanData($aTransactionData)));
            $aData['date'] = date('Y-m-d H:i:s', $aTransactionData['txtime']);
            return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
        }
        return false;
    }

    /**
     * Returns transaction states
     *
     * @return array
     */
    public static function getStates()
    {
        return self::$aStates;
    }

    /**
     * Updates the order id
     *
     * @param $iTxId
     * @param $iOrderId
     * @return bool
     */
    public function updateOrderId($iTxId, $iOrderId)
    {
        $aData = array(
            'id_order' => \pSQL($iOrderId)
        );
        $iTxId = (int)$iTxId;
        return (bool)\Db::getInstance()->update(self::getTable(), $aData, "txid = '{$iTxId}'");
    }

    /**
     * Updates the referemce
     *
     * @param $iTxId
     * @param $sReference
     * @return bool
     */
    public function updateReference($iTxId, $sReference)
    {
        $aData = array(
            'reference' => \pSQL($sReference)
        );
        $iTxId = (int)$iTxId;
        return (bool)\Db::getInstance()->update(self::getTable(), $aData, "txid = '{$iTxId}'");
    }

    /**
     * Returns array with all transactions for givin order
     *
     * @param string $sIdent
     * @param string $sField
     * @return array
     */
    public function getTransactions($sIdent, $sField = 'id_order')
    {
        $sTable = _DB_PREFIX_ . self::getTable();
        $sField = \bqSQL($sField);
        $sIdent = \pSQL($sIdent);
        $sQ = "select * from $sTable where `{$sField}` = '{$sIdent}' order by date asc";
        $aTransactions = \DB::getInstance()->executeS($sQ);
        $aTransactionList = array();
        if ($aTransactions && is_array($aTransactions) && count($aTransactions) > 0) {
            foreach ($aTransactions as $aTransactionData) {
                $aTransactionList[] = $this->getProcessedTransactionData($aTransactionData);
            }
        }
        return $aTransactionList;
    }

    /**
     * Returns process transaction data
     *
     * @param $aTransaction
     * @return mixed
     */
    protected function getProcessedTransactionData($aTransaction)
    {
        $aData = \Tools::jsonDecode($aTransaction['data'], true);
        $aTransaction['data'] = $aData;
        return $aTransaction;
    }

    /**
     * Returns array with all transactions for given order
     *
     * @param string $sIdent
     * @param string $sField
     * @return array
     */
    public function getAdminTransactions($sIdent, $sField = 'id_order')
    {
        $aTransactions = $this->getTransactions($sIdent, $sField);
        if (count($aTransactions) == 0) {
            return array();
        }
        $aAdminTransactions = array();
        $dLastReceivable = 0;
        $dLastPayment = 0;
        foreach ($aTransactions as $aTransaction) {
            $aData = $aTransaction['data'];

            $dReceivable = (double)$aData['receivable'];
            $dBalance = (double)$aData['balance'];
            $dPayment = $dReceivable - $dBalance;

            if ($dLastReceivable != $dReceivable || ($dLastReceivable == $dReceivable && $dPayment == $dLastPayment)) {
                $aAdminTransaction1 = $aTransaction;
                $aAdminTransaction1['txaction_langident'] = $this->getActionLangIdent(
                    'receivable',
                    $aAdminTransaction1['txaction'],
                    ($dReceivable - $dLastReceivable)
                );
                $aAdminTransaction1['calculated_receivable'] = ($dReceivable - $dLastReceivable);
                $aAdminTransactions[] = $aAdminTransaction1;
            }
            if ($dPayment != $dLastPayment) {
                $aAdminTransaction2 = $aTransaction;
                $aAdminTransaction2['txaction_langident'] = $this->getActionLangIdent(
                    'payment',
                    $aAdminTransaction2['txaction'],
                    ($dPayment - $dLastPayment)
                );
                $aAdminTransaction2['calculated_payment'] = ($dPayment - $dLastPayment);
                $aAdminTransactions[] = $aAdminTransaction2;
            }

            $dLastReceivable = $dReceivable;
            $dLastPayment = $dPayment;
        }
        return $aAdminTransactions;
    }

    /**
     * Returns lang ident for action
     * @param $sType
     * @param $sAction
     * @param $dAmount
     * @return string
     */
    protected function getActionLangIdent($sType, $sAction, $dAmount)
    {

        if ($sAction == 'refund') {
            $sAction = 'debit';
        }

        $aLangMapping = $this->getActionLangMapping();
        if (count($aLangMapping) > 0 && isset($aLangMapping[$sType]) && isset($aLangMapping[$sType][$sAction])) {
            $aActionMapping = $aLangMapping[$sType][$sAction];
            if ($dAmount > 0) {
                $iIndex = 0;
            } else {
                $iIndex = 1;
            }
            return $aActionMapping[$iIndex];
        }
    }


    /**
     * Returns first transaction
     *
     * @param string $sIdent
     * @param string $sField
     * @return array|mixed
     */
    public function getFirstTransaction($sIdent, $sField = 'id_order')
    {
        $sField = \bqSQL($sField);
        $sIdent = \pSQL($sIdent);
        $sTable = _DB_PREFIX_ . self::getTable();
        $sQ = "select * from $sTable where `{$sField}` = '{$sIdent}' order by date asc";
        $aRawTransaction = \DB::getInstance()->getRow($sQ);
        $aTransaction = array();
        if ($aRawTransaction && is_array($aRawTransaction) && count($aRawTransaction) > 0) {
            $aTransaction = $this->getProcessedTransactionData($aRawTransaction);
        }
        return $aTransaction;
    }
}
