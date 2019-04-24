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

use Payone\Base\Registry;

class Base extends \Payone\Request\Builder\Base
{

    /**
     * Is it an generic paypal request
     *
     * @var bool
     */
    protected $blIsGenericRequest = false;

    /**
     * Work order id
     *
     * @var bool
     */
    protected $sWorkOrderId = null;

    /**
     * Builds payment request
     */
    public function build()
    {
        parent::build();
        $this->setAuthToRequest();
        $this->setBasePaymentToRequest();
        if ($this->sendBasketContent()) {
            $this->setItemsToRequest();
        }
    }


    /**
     * Sets generic request to true/false
     *
     * @param $blIsGeneric
     */
    public function setGenericRequest($blIsGeneric)
    {
        $this->blIsGenericRequest = $blIsGeneric;
    }

    /**
     * Returns true if request is generic
     *
     * @return bool
     */
    protected function isGenericRequest()
    {
        return $this->blIsGenericRequest;
    }

    /**
     * Sets work order id
     *
     * @param string $sWorkOrderId
     */
    public function setWorkOrderId($sWorkOrderId)
    {
        $this->sWorkOrderId = $sWorkOrderId;
    }

    /**
     * Returns work order id
     *
     * @return bool
     */
    protected function getWorkOrderId()
    {
        return $this->sWorkOrderId;
    }

    /**
     * Returns ordertoal converted amount
     *
     * @return double
     */
    public function getOrderTotal()
    {
        $dAmount = 0;
        $oCart = $this->getCart();
        if ($oCart->id) {
            $dAmount = Registry::getHelper()->getConvertedAmount((float)$oCart->getOrderTotal(true, \Cart::BOTH));
        }
        return $dAmount;
    }

    /**
     * Adds redirect parameters
     */
    protected function addRedirectParameters()
    {
        $oPayment = $this->getPayment();
        $sToken = \Tools::getToken(false);
        $aBaseParams = array(
            'payone_payment' => $oPayment->getId(),
            'payone_token' => $sToken,
        );
        if ($oPayment->getParentId()) {
            $oParentPayment = Registry::getPayment()->getParentPaymentMethod($oPayment->getParentId());
            if ($oParentPayment) {
                $aBaseParams['payone_payment'] = $oParentPayment->getId();
                $aBaseParams['payone_payment_sub'] = $oPayment->getId();
            }
        }

        $this->setParam('successurl', $this->getSuccessUrl($aBaseParams));
        $this->setParam('errorurl', $this->getErrorUrl($aBaseParams));
        $this->setParam('backurl', $this->getBackUrl($aBaseParams));
    }

    /**
     * Returns success url
     * @param array $aBaseParams
     * @return string
     */
    protected function getSuccessUrl($aBaseParams)
    {
        $aBaseParams['payone_redirect'] = 'success';
        return Registry::getHelper()->buildModuleUrl(
            $this->getPayment()->getController(),
            $aBaseParams
        );
    }

    /**
     * Returns error url
     * @param array $aBaseParams
     * @return string
     */
    protected function getErrorUrl($aBaseParams)
    {
        $aBaseParams['payone_redirect'] = 'error';
        return Registry::getHelper()->buildModuleUrl(
            $this->getPayment()->getController(),
            $aBaseParams
        );
    }

    /**
     * Returns back url
     * @param array $aBaseParams
     * @return string
     */
    protected function getBackUrl($aBaseParams)
    {
        $aBaseParams['payone_redirect'] = 'back';
        return Registry::getHelper()->buildModuleUrl(
            $this->getPayment()->getController(),
            $aBaseParams
        );
    }

    /**
     * Sets user to request
     */
    protected function setUserToRequest()
    {
        $oRequestBuilder = new \Payone\Request\Builder\User;
        $oRequestBuilder->setCart($this->getCart());
        $oRequestBuilder->build();
        $aParams = $oRequestBuilder->getParams();
        if (is_array($aParams) && count($aParams) > 0) {
            foreach ($aParams as $sKey => $sValue) {
                $this->setParam($sKey, $sValue);
            }
        }
    }

    /**
     * Sets auth to request
     */
    protected function setAuthToRequest()
    {
        $oRequestBuilder = new \Payone\Request\Builder\Auth;
        $oRequestBuilder->build();
        $aParams = $oRequestBuilder->getParams();
        if (is_array($aParams) && count($aParams) > 0) {
            foreach ($aParams as $sKey => $sValue) {
                $this->setParam($sKey, $sValue);
            }
        }
    }

    /**
     * Sets items to request
     */
    protected function setItemsToRequest()
    {
        $oRequestBuilder = new \Payone\Request\Builder\Items;
        $oRequestBuilder->setCart($this->getCart());
        $oRequestBuilder->build();
        $aParams = $oRequestBuilder->getParams();
        if (is_array($aParams) && count($aParams) > 0) {
            foreach ($aParams as $sKey => $sValue) {
                $this->setParam($sKey, $sValue);
            }
        }
    }

    /**
     * Sets base payment info to request
     */
    protected function setBasePaymentToRequest()
    {
        if ($this->isGenericRequest()) {
            $this->setParam('request', 'genericpayment');
        } else {
            $this->setParam('request', $this->getPayment()->getRequestType());
        }

        $this->setParam('mode', $this->getConnectionMode());
        $this->setParam('clearingtype', $this->getPayment()->getClearingType());
        $this->setParam('reference', $this->getReference());
        $this->setParam('amount', $this->getOrderTotal());
        $this->setParam('currency', $this->getCurrency()->iso_code);
    }

    /**
     * Returns connection mode
     *
     * @return string
     */
    protected function getConnectionMode()
    {
        return $this->getPayment()->getMode();
    }

    /**
     * Sets payment specific data to request
     */
    protected function setPaymentDataToRequest()
    {
    }
}
