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

namespace Payone\Request\Builder\Order;

class Debit extends Base
{
    /**
     * Bank data
     *
     * @var array
     */
    protected $aBankData = null;

    /**
     * Sets bank data
     *
     * @param $aData
     */
    public function setBankData($aData)
    {
        $this->aBankData = $aData;
    }

    /**
     * Returns array with bank data
     *
     * @return array
     */
    protected function getBankData()
    {
        return $this->aBankData;
    }

    /**
     * Sets user params
     */
    public function build()
    {
        parent::build();
        $this->setParam('request', 'debit');
        $this->setParam('transactiontype', 'GT');
        $this->setBankDataToRequest();

        if ($this->getPayment()->isItemsRequiredInDebitRequest()) {
            $this->setItemsFromFirstRequestToRequest();
        }
        if ($this->getPayment()->getId() == 'bsinvoice') {
            $this->setAuthForPOVToRequest();
        }
    }

    /**
     * Sets order data to request
     */
    protected function setBankDataToRequest()
    {
        $aBankData = $this->getBankData();
        if ($aBankData && count($aBankData) > 0 &&
            isset($aBankData['bankaccount']) && isset($aBankData['bankcountry'])) {
            foreach ($aBankData as $sParam => $sValue) {
                $this->setParam($sParam, $sValue);
            }
        }
    }
}
