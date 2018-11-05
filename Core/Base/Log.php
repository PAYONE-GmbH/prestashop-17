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

class Log
{
    /**
     * Message
     *
     * Severity
     * 1. Info
     * 2. Warning
     * 3. Error
     * 4. Blocker
     *
     * TODO add own logging if needed
     *
     * @param $sMessage
     * @param int $iSeverity
     * @param array $aPrestaLog Array with Prestashop [Errorcode, Objectype, ObjectId]
     */
    public function log($sMessage, $iSeverity = 1, $aPrestaLog = null)
    {
        if (isset($aPrestaLog) && count($aPrestaLog) > 0) {
            $sMessagePrefix = 'PAYONE: ';
            $sErrorCode = isset($aPrestaLog[0]) && $aPrestaLog[0] != null ? $aPrestaLog[0] : null;
            $sObjectType = isset($aPrestaLog[1]) && $aPrestaLog[1] != null ? $aPrestaLog[1] : null;
            $iObjectId = isset($aPrestaLog[2]) && $aPrestaLog[2] != null ? $aPrestaLog[2] : null;
            \PrestaShopLogger::addLog(
                $sMessagePrefix . $sMessage,
                $iSeverity,
                $sErrorCode,
                $sObjectType,
                (int)$iObjectId
            );
        }
    }
}
