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

namespace Payone\Validation;

use Payone\Base\Registry;
use Payone\Validation\Request\Request as RequestValidation;

class Validation
{

    /**
     * Validation after redirect flag
     *
     * @var bool
     */
    protected $blAfterRedirect = false;

    /**
     * Payone ips for status update
     *
     * @var array
     */
    protected $aWhiteListIps = array(
        '185.60.20.*',
        '213.178.72.196',
        '213.178.72.197',
        '217.70.200.*',
    );

    /**
     * Error setter wrapper
     * @param string $sType
     * @param string $sMessage
     * @param boolean $blTranslate
     */
    protected function setError($sType, $sMessage, $blTranslate = false)
    {
        Registry::getErrorHandler()->setError($sType, $sMessage, $blTranslate);
    }

    /**
     * Sets validation mode to after redirect
     *
     * @param boolean $blIsAfterRedirect
     */
    public function setAfterRedirect($blIsAfterRedirect)
    {
        $this->blAfterRedirect = $blIsAfterRedirect;
    }

    /**
     * Returns true if validation is in after redirect mode
     *
     * @return boolean
     */
    protected function isAfterRedirect()
    {
        return $this->blAfterRedirect;
    }

    /**
     * Validate givin payment
     *
     * @param object $oPayment
     * @return boolean
     */
    public function validatePayment($oPayment)
    {
        if ($oPayment) {
            $sValidationClass = $oPayment->getValidationClass();

            if (!class_exists($sValidationClass) &&
                ($oParentPayment = Registry::getPayment()->getParentPaymentMethod($oPayment->getParentId()))
            ) {
                $sValidationClass = $oParentPayment->getValidationClass();
            }

            if (!class_exists($sValidationClass)) {
                $sValidationClass = 'Payone\Validation\Payment\PaymentDefault';
            }

            if (class_exists($sValidationClass) && ($oValidation = new $sValidationClass)) {
                $oValidation->setValidationPayment($oPayment);
                $oValidation->setAfterRedirect($this->isAfterRedirect());
                return $oValidation->validate();
            }
        }
        return false;
    }

    /**
     * Validates checkout process
     *
     * @param object $oContext
     * @param string $sToken
     *
     * @return boolean
     */
    public function validateCheckout($oContext, $sToken = null)
    {

        $blValidToken = $this->validateCheckoutToken($oContext, $sToken);
        if (!$blValidToken) {
            $this->setError('validation', 'FC_PAYONE_ERROR_TOKEN', true);
            return false;
        }
        $blValidPayment = $this->validateUserSelectedPayment();
        $oCart = $oContext->cart;
        $blValidCart = $this->validateCart($oCart);

        $blValidUser = $this->validateUser($oContext->customer);

        return $blValidPayment && $blValidCart && $blValidUser;
    }

    /**
     * Validates checkout token
     *
     * @param object $oContext
     * @param string $sToken
     * @return boolean
     */
    protected function validateCheckoutToken($oContext, $sToken = null)
    {
        if (!$sToken) {
            $sToken = \Tools::getValue('payone_token');
        }
        if (!$sToken) {
            return false;
        }
        $sActToken = \Tools::getToken(false, $oContext);
        if ($sActToken != $sToken) {
            return false;
        }
        return true;
    }

    /**
     * Validates cart
     *
     * @param object $oCart
     *
     * @return boolean
     */
    protected function validateCart($oCart)
    {
        if ($oCart->id_customer == 0 || $oCart->id_address_delivery == 0 || $oCart->id_address_invoice == 0) {
            $this->setError('validation', 'FC_PAYONE_ERROR_NO_VALID_CART', true);
            return false;
        }
        return true;
    }

    /**
     * Validate selected payment
     *
     */
    protected function validateUserSelectedPayment()
    {
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        if (!$oSelectedPayment) {
            $this->setError('validation', 'FC_PAYONE_ERROR_NO_PAYMENT_SELECTED', true);
            return false;
        }
        return true;
    }

    /**
     * Validates user
     *
     * @param object $oCustomer
     *
     * @return boolean
     */
    protected function validateUser($oCustomer)
    {
        if (!\Validate::isLoadedObject($oCustomer)) {
            $this->setError('validation', 'FC_PAYONE_ERROR_NO_USER_FOUND', true);
            return false;
        }

        if (($oCart = \Context::getContext()->cart) &&
            ($aSummary = $oCart->getSummaryDetails()) &&
            $aSummary['is_virtual_cart'] == 1 &&
            !\Validate::isEmail($oCustomer->email)
        ) {
            $this->setError('validation', 'FC_PAYONE_ERROR_VIRTUAL_CART_NEEDS_EMAIL', true);
            return false;
        }

        return true;
    }


    /**
     * Triggers request validation
     *
     * @param array $aRequest
     * @return bool
     */
    public function validateRequest($aRequest)
    {
        $oValidation = new RequestValidation();
        return $oValidation->validateRequest($aRequest);
    }

    /**
     * Check currency against available
     *
     * @param object $oCurrency
     * @param array $aCurrenciesModule
     * @return boolean
     */
    public function validateCurrency($oCurrency, $aCurrenciesModule)
    {
        if (is_array($aCurrenciesModule)) {
            foreach ($aCurrenciesModule as $aCurrencyModule) {
                if ($oCurrency->id == $aCurrencyModule['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check country against available against payment
     *
     * @param string $sPayment
     * @return boolean
     */
    public function validatePaymentCountry($sPayment)
    {
        $sUserCountryId = $this->getUserCountryId();
        if (!$sPayment || !$sUserCountryId) {
            return false;
        }
        $sCountry = \Configuration::get('FC_PAYONE_PAYMENT_COUNTRY_' . \Tools::strtoupper($sPayment));
        if (is_string($sCountry)) {
            $aCountry = \Tools::jsonDecode($sCountry, true);
            if (is_array($aCountry) && in_array($sUserCountryId, $aCountry)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns user country id
     *
     * @return int country id
     */
    protected function getUserCountryId()
    {
        $oCart = \Context::getContext()->cart;
        if (!$oCart) {
            return;
        }
        $oPrestaHelper = Registry::getHelperPrestashop();
        $oAddress = $oPrestaHelper->fcPayoneGetAddress((int)$oCart->id_address_invoice);
        if ($oAddress) {
            return (int)$oAddress->id_country;
        }
    }

    /**
     * Returns true
     *
     * @param string $sCheckIp
     * @return bool
     */
    public function isValidPayoneIp($sCheckIp)
    {
        $aWhiteList = $this->aWhiteListIps;
        if ($sCheckIp && array_search($sCheckIp, $aWhiteList) === false &&
            $this->searchIPInList($sCheckIp, $aWhiteList) === true
        ) {
            return true;
        }
        return false;
    }

    /**
     * Search IP in list
     *
     * @param $sCheckIp
     * @param $aList
     * @return bool
     */
    protected function searchIPInList($sCheckIp, $aList)
    {
        $blMatch = false;
        foreach ($aList as $sIP) {
            if (stripos($sIP, '*') !== false) {
                $sDelimiter = '/';

                $sRegex = preg_quote($sIP, $sDelimiter);
                $sRegex = str_replace('\*', '\d{1,3}', $sRegex);
                $sRegex = $sDelimiter . '^' . $sRegex . '$' . $sDelimiter;

                preg_match($sRegex, $sCheckIp, $aMatches);
                if (is_array($aMatches) && count($aMatches) == 1 && $aMatches[0] == $sCheckIp) {
                    $blMatch = true;
                }
            }
        }
        return $blMatch;
    }

    /**
     * Returns true if mandate download is valid
     *
     * @param $iOrderId
     * @param $iCustomerId
     * @param $sIdent
     *
     * @return boolean
     */
    public function validateMandateDownload($iOrderId, $iCustomerId, $sIdent)
    {
        return $this->isMandateDownloadUserValid($iOrderId, $iCustomerId) &&
            $this->isMandateDownloadIdentValid($iOrderId, $sIdent);
    }

    /**
     * Check if userid matches userid from order
     *
     * @param $iOrderId
     * @param $iCustomerId
     * @return bool
     */
    protected function isMandateDownloadUserValid($iOrderId, $iCustomerId)
    {
        $oUser = new \Payone\Base\User();
        $iUserId = $oUser->getPayoneUserIdByCustomerId($iCustomerId);
        $sTable = _DB_PREFIX_ . \Payone\Base\Order::getTable();
        $iCleanOrderId = (int)\pSQL($iOrderId);
        $sQ = "select userid from $sTable where id_order = '{$iCleanOrderId}'";
        $iOrderUserId = \Db::getInstance()->getValue($sQ);
        if ($iUserId == $iOrderUserId) {
            return true;
        }
    }

    /**
     * Checks if given ident is valid
     *
     * @param $iOrderId
     * @param $sIdent
     * @return bool
     */
    protected function isMandateDownloadIdentValid($iOrderId, $sIdent)
    {
        $sTable = _DB_PREFIX_ . \Payone\Base\Mandate::getTable();
        $iCleanOrderId = (int)\pSQL($iOrderId);
        $sQ = "select mandate_identifier from $sTable where id_order = '{$iCleanOrderId}'";
        $sMandateIdentifier = \Db::getInstance()->getValue($sQ);
        if ($sIdent == $sMandateIdentifier) {
            return true;
        }
    }
}
