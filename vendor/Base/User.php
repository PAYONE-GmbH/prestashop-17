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

namespace Payone\Base;

class User
{
    /**
     * DB Table
     *
     * @var string
     */
    protected static $sTable = 'fcpayoneusers';

    /**
     * Prestashop customer id
     *
     * @var int
     */
    protected $iCustomerId = null;

    /**
     * Payone user id
     *
     * @var int
     */
    protected $iPayoneUserId = null;

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
     * Sets customer id
     *
     * @param $iId
     */
    public function setCustomerId($iId)
    {
        $this->iCustomerId = $iId;
    }

    /**
     * Returns customer id
     *
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->iCustomerId;
    }

    /**
     * Sets payone user id
     *
     * @param int $iId
     */
    public function setPayoneUserId($iId)
    {
        $this->iPayoneUserId = $iId;
    }

    /**
     * Returns payone user id
     *
     * @return int
     */
    protected function getPayoneUserId()
    {
        return $this->iPayoneUserId;
    }

    /**
     * Get payone userid by customerid
     *
     * @param int $sRawCustomerId
     * @return int
     */
    public function getPayoneUserIdByCustomerId($sRawCustomerId)
    {
        $sCustomerId = (int)\pSQL($sRawCustomerId);
        $sTable = _DB_PREFIX_ . self::getTable();
        $sQ = "SELECT userid FROM $sTable WHERE id_customer='{$sCustomerId}'";
        $aResult = \Db::getInstance()->getRow($sQ);
        if ($aResult['userid']) {
            return $aResult['userid'];
        }
    }

    /**
     * Get payone customerid by userid
     *
     * @param int $sRawUserId
     * @return int
     */
    public function getCustomerIdByPayoneUserId($sRawUserId)
    {
        $sUserId = (int)\pSQL($sRawUserId);
        $sTable = _DB_PREFIX_ . self::getTable();
        $sQ = "SELECT id_customer FROM $sTable WHERE userid='{$sUserId}'";
        $aResult = \Db::getInstance()->getRow($sQ);
        if ($aResult['id_customer']) {
            return $aResult['id_customer'];
        }
    }

    /**
     * saves user to db
     *
     * @return boolean
     */
    public function save()
    {
        $aData = array();
        $aData['userid'] = \pSQL($this->getPayoneUserId());
        $aData['id_customer'] = \pSQL($this->getCustomerId());
        $aData['date'] = date('Y-m-d H:i:s');
        return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
    }
}
