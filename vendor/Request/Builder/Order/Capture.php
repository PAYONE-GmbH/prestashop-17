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

class Capture extends Base
{

    /**
     * Settle account
     *
     * @var bool
     */
    protected $blSettleAccount = true;

    /**
     * Sets account settlement
     *
     * @param $blSettle
     */
    public function setAccountSettlement($blSettle)
    {
        $this->blSettleAccount = $blSettle;
    }

    /**
     * Sets account settlement
     *
     * @return boolean
     */
    protected function getAccountSettlement()
    {
        return $this->blSettleAccount;
    }

    /**
     * Sets user params
     */
    public function build()
    {
        parent::build();
        $this->setParam('request', 'capture');
        if ($this->getPayment()->getId() == 'bsinvoice') {
            $this->setAuthForPOVToRequest();
        }
    }

    /**
     * Sets order data to request
     */
    protected function setOrderDataToRequest()
    {
        $blReturn = parent::setOrderDataToRequest();
        if ($blReturn) {
            if ($this->getAccountSettlement()) {
                $this->setParam('settleaccount', 'auto');
            } else {
                $this->setParam('settleaccount', 'no');
            }
        }

        if ( $this->getPayment()->isItemsRequiredInCaptureRequest() ) {
            $this->setItemsFromFirstRequestToRequest();
        }

        return $blReturn;
    }
}
