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

class Order
{
    /**
     * DB Table
     *
     * @var string
     */
    protected static $sTable = 'fcpayoneorders';

    /**
     * Order Id
     *
     * @var int
     */
    protected $iOrderId = null;

    /**
     * Order Reference
     *
     * @var string
     */
    protected $sOrderReference = null;

    /**
     * Payment Id
     *
     * @var string
     */
    protected $sPaymentId = null;

    /**
     * Transaction id
     *
     * @var null
     */
    protected $iTxId = null;

    /**
     * Payone user id
     *
     * @var int
     */
    protected $iUserId = null;

    /**
     * Request type
     *
     * @var string
     */
    protected $sRequestType = null;

    /**
     * Reference
     *
     * @var string
     */
    protected $sReference = null;

    /**
     * Sets orderid
     *
     * @param int $iOrderId
     */
    public function setOrderId($iOrderId)
    {
        $this->iOrderId = $iOrderId;
    }

    /**
     * Returns orderid
     *
     * @return int
     */
    protected function getOrderId()
    {
        return $this->iOrderId;
    }

    /**
     * Order reference
     *
     * @param $sOrderReference
     */
    public function setOrderReference($sOrderReference)
    {
        $this->sOrderReference = $sOrderReference;
    }

    /**
     * Returns order reference
     *
     * @return mixed
     */
    protected function getOrderReference()
    {
        return $this->sOrderReference;
    }

    /**
     * Sets payment id
     *
     * @param string $sPaymentId
     */
    public function setPaymentId($sPaymentId)
    {
        $this->sPaymentId = $sPaymentId;
    }

    /**
     * Returns payment id
     *
     * @return string
     */
    protected function getPaymentId()
    {
        return $this->sPaymentId;
    }


    /**
     * Sets transaction id
     *
     * @param int $iTxId
     */
    public function setTxId($iTxId)
    {
        $this->iTxId = $iTxId;
    }

    /**
     * Returns transaction id
     *
     * @return int
     */
    protected function getTxId()
    {
        return $this->iTxId;
    }

    /**
     * Sets userid
     *
     * @param int $iUserId
     */
    public function setUserId($iUserId)
    {
        $this->iUserId = $iUserId;
    }

    /**
     * Returns user id
     *
     * @return int
     */
    protected function getUserId()
    {
        return $this->iUserId;
    }

    /**
     * Sets request type
     *
     * @param $sRequestType
     */
    public function setRequestType($sRequestType)
    {
        $this->sRequestType = $sRequestType;
    }

    /**
     * Returns request type
     *
     * @return mixed
     */
    protected function getRequestType()
    {
        return $this->sRequestType;
    }

    /**
     * Sets mode
     *
     * @param $sMode
     */
    public function setMode($sMode)
    {
        $this->sMode = $sMode;
    }

    /**
     * Returns mode
     *
     * @return mixed
     */
    protected function getMode()
    {
        return $this->sMode;
    }

    /**
     * Sets reference
     *
     * @param $sReference
     */
    public function setReference($sReference)
    {
        $this->sReference = $sReference;
    }

    /**
     * Returns reference
     */
    protected function getReference()
    {
        return $this->sReference;
    }

    /**
     * Returns table
     *
     * @return string
     */
    public static function getTable()
    {
        return self::$sTable;
    }

    /**
     * saves order to db
     *
     * @return boolean
     */
    public function save()
    {
        $aData = array();
        $aData['id_order'] = \pSQL($this->getOrderId());
        $aData['reference_order'] = \pSQL($this->getOrderReference());
        $aData['reference'] = \pSQL($this->getReference());
        $aData['paymentid'] = \pSQL($this->getPaymentId());
        $aData['txid'] = \pSQL($this->getTxId());
        $aData['userid'] = \pSQL($this->getUserId());
        $aData['requesttype'] = \pSQL($this->getRequestType());
        $aData['mode'] = \pSQL($this->getMode());
        $aData['date'] = date('Y-m-d H:i:s');
        return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
    }

    /**
     * Returns order data
     *
     * @param $sTxId
     * @return mixed
     */
    public function getOrderDataByTxId($sTxId)
    {
        if ($sTxId) {
            $sTable = _DB_PREFIX_ . self::getTable();
            $sCleanTxId = (int)\pSQL($sTxId);
            $sQ = "select * from " . $sTable . " where txid = '{$sCleanTxId}'";
            $aRow = \Db::getInstance()->getRow($sQ);
            if ($aRow) {
                return $aRow;
            }
        }
    }

    /**
     * Returns order data
     *
     * @param $iOrderId
     * @return mixed
     */
    public function getOrderData($iOrderId)
    {
        if ($iOrderId) {
            $sTable = _DB_PREFIX_ . self::getTable();
            $sCleanOrderId = (int)\pSQL($iOrderId);
            $sQ = "select * from " . $sTable . " where id_order = '{$sCleanOrderId}'";
            $aRow = \Db::getInstance()->getRow($sQ);
            if ($aRow) {
                return $aRow;
            }
        }
    }

    /**
     * Returns unformatted request amount for txid
     *
     * @param $iRawTxId
     * @return int
     */
    public function getOrderRequestAmount($iRawTxId)
    {
        if ($iRawTxId) {
            $iTxId = (int)\pSQL($iRawTxId);
            $sTable = _DB_PREFIX_ . \Payone\Request\Request::getTable();
            $sQ = "select request from " . $sTable .
                " where txid = '{$iTxId}' and (status = 'APPROVED' || status = 'REDIRECT') order by date asc";
            $aRow = \Db::getInstance()->getRow($sQ);
            if (isset($aRow['request'])) {
                $oRequest = \Tools::jsonDecode($aRow['request']);
                return (float)$oRequest->amount;
            }
        }
        return 0;
    }

    /**
     * Returns formatted request order amount
     *
     * @param $iTxId
     * @return float
     */
    public function getFormattedRequestAmount($iTxId)
    {
        if (($iAmount = $this->getOrderRequestAmount($iTxId))) {
            $iCalcAmount = ($iAmount / 100);
            return number_format($iCalcAmount, 2, '.','');
        }
    }
}
