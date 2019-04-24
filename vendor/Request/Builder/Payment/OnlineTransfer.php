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

namespace Payone\Request\Builder\Payment;

class OnlineTransfer extends Base
{

    /**
     * Builds payment request
     */
    public function build()
    {
        parent::build();
        $this->setParam('narrative_text', $this->getPayment()->getTitle());
        $this->setUserToRequest();
        $this->setPaymentDataToRequest();
        $this->addRedirectParameters();
    }

    /**
     * Sets debit payment data to request
     */
    public function setPaymentDataToRequest()
    {
        $oPayment = $this->getPayment();
        $aPaymentInfo = $this->getForm();
        if ($oPayment->hasIbanBic()) {
            $this->setParam('iban', $aPaymentInfo['iban']);
            $this->setParam('bic', $aPaymentInfo['bic']);
        }

        if (count($oPayment->getBankGroups()) > 0) {
            $this->setParam('bankgrouptype', $aPaymentInfo['bankgrouptype_' . $oPayment->getId()]);
        }

        $this->setParam('bankcountry', $this->getParam('country'));
        $this->setParam('onlinebanktransfertype', $oPayment->getSubClearingType());
    }
}
