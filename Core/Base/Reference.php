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

class Reference
{

    /**
     * Table
     *
     * @var string
     */
    protected static $sTable = 'fcpayonereferences';

    /**
     * Reference number
     * @var int
     */
    protected $iReferenceNumber = null;

    /**
     * TxId
     *
     * @var int
     */
    protected $iTxId = null;

    /**
     * Creates new reference
     */
    public function createReference()
    {
        $iMaxReference = (int)$this->getMaxReference();
        $iReferenceNumber = $iMaxReference + 1;
        $this->setReferenceNumber($iReferenceNumber);
        $this->save();
    }

    /**
     * Sets refernce number
     *
     * @param int $iNumber
     */
    protected function setReferenceNumber($iNumber)
    {
        $this->iReferenceNumber = $iNumber;
    }

    /**
     * Returns refernce number
     *
     * @return int $iNumber
     */
    protected function getReferenceNumber()
    {
        return $this->iReferenceNumber;
    }

    /**
     * Sets txid
     *
     * @param int $iTxId
     */
    protected function setTxId($iTxId)
    {
        $this->iTxId = $iTxId;
    }

    /**
     * Returns txid
     *
     * @return int $iNumber
     */
    protected function getTxId()
    {
        return $this->iTxId;
    }

    /**
     * Returns max reference for prefix
     *
     * @return int
     */
    protected function getMaxReference()
    {
        $sReferencePrefix = \pSQL($this->getReferencePrefix());
        $sTable = _DB_PREFIX_ . self::getTable();
        $sQ = "select max(reference) as reference from $sTable where reference_prefix='{$sReferencePrefix}'";
        $aRow = \Db::getInstance()->getRow($sQ);
        return $aRow['reference'];
    }

    /**
     * Returns refrence_prefix
     *
     * @return string
     */
    protected function getReferencePrefix()
    {
        return \Configuration::get('FC_PAYONE_CONNECTION_REF_PREFIX');
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
     * Returns new reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->getReferencePrefix() . $this->getReferenceNumber();
    }

    /**
     * saves reference to db
     *
     * @return boolean
     */
    protected function save()
    {
        $aData = array();
        $aData['reference'] = \pSQL($this->getReferenceNumber());
        $aData['reference_prefix'] = \pSQL($this->getReferencePrefix());
        $aData['txid'] = \pSQL($this->getTxId());
        $aData['date'] = date('Y-m-d H:i:s');
        return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
    }

    /**
     * Updates reference with txid
     *
     * @param $sReference
     * @param $sTxId
     */
    public static function updateTxId($sReference, $sTxId)
    {
        $sReference = \pSQL($sReference);
        \Db::getInstance()->update(
            self::getTable(),
            array('txid' => (int) $sTxId),
            "concat(reference_prefix,reference) = '{$sReference}'"
        );
    }
}
