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

class PayPalExpress extends Base
{

    /**
     * Request action
     * expresscheckout|execution
     *
     * @var null
     */
    protected $sAction = null;

    /**
     * Builds payment request
     */
    public function build()
    {
        parent::build();
        $this->setParam('narrative_text', $this->getPayment()->getTitle());
        if ($this->getAction() == 'execution') {
            $this->setUserToRequest();
        }
        $this->setPaymentDataToRequest();
        $this->addRedirectParameters();
    }

    /**
     * Set generic paypal request params
     */
    protected function setPaymentDataToRequest()
    {
        $this->setParam('wallettype', $this->getPayment()->getSubClearingType());
        $sWorkOrderId = $this->getWorkOrderId();
        if ($this->getAction() == 'execution') {
            $this->setParam('workorderid', $sWorkOrderId);
        } else {
            if ($sWorkOrderId) {
                $this->setParam('workorderid', $sWorkOrderId);
                $this->setParam('add_paydata[action]', 'getexpresscheckoutdetails');
            } else {
                $this->setParam('add_paydata[action]', 'setexpresscheckout');
            }
        }
    }

    /**
     * Returns error url
     * @param array $aBaseParams
     * @return string
     */
    protected function getErrorUrl($aBaseParams)
    {
        $aBaseParams['payone_redirect_order'] = true;
        return parent::getErrorUrl($aBaseParams);
    }

    /**
     * Returns back url
     * @param array $aBaseParams
     * @return string
     */
    protected function getBackUrl($aBaseParams)
    {
        $aBaseParams['payone_redirect_order'] = true;
        return parent::getBackUrl($aBaseParams);
    }

    /**
     * Sets action for paypal express request
     *
     * @param $sAction
     */
    public function setAction($sAction)
    {
        $this->sAction = $sAction;
    }

    /**
     * Returns action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->sAction;
    }
}
