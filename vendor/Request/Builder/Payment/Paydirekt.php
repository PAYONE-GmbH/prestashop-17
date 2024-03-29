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

namespace Payone\Request\Builder\Payment;

class Paydirekt extends Base
{

    /**
     * Builds payment request
     */
    public function build()
    {
        parent::build();
        $this->setParam('narrative_text', 'Giropay');
        $this->setUserToRequest();
        $this->setPaymentDataToRequest();
        $this->addRedirectParameters();
    }

    /**
     * Set paypal request params
     */
    protected function setPaymentDataToRequest()
    {
        $this->setParam('wallettype', $this->getPayment()->getSubClearingType());
        $aSummary = $this->getCart()->getSummaryDetails();
        if ($aSummary['is_virtual_cart'] == 1) {
            $this->setParam('add_paydata[shopping_cart_type]', 'DIGITAL');
        }
    }
}
