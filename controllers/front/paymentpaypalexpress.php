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

use Payone\Base\Registry;
use Payone\Request\Request;
use Payone\Response\Response;

require_once dirname(__FILE__) . '/payment.php';

class FcPayonePaymentPayPalExpressModuleFrontController extends FcPayonePaymentModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        Context::getContext()->cookie->sFcPayoneUserAgent = $_SERVER['HTTP_USER_AGENT'];
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $blSuccess = $this->fcPayonePaymentPreProcess($oSelectedPayment);
        if ($blSuccess) {
            $this->fcPostProcessPayPalExpress();
        }
    }

    /**
     * Add payment specific checks
     *
     * @param object $oSelectedPayment
     * @param boolean $blAfterRedirect
     * @return bool
     */
    protected function fcPayonePaymentSpecificChecks($oSelectedPayment, $blAfterRedirect = false)
    {
        if (!Tools::isSubmit('cgv')) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_TERMS_NOT_ACCEPTED', true);
            return false;
        }
        return true;
    }

    /**
     * Checks if paypal express redirect is successfull
     *
     * @return bool
     */
    protected function fcPayoneCheckPayPalExpressRedirect()
    {
        if (\Tools::getValue('payone_redirect') == 'back' || \Tools::getValue('payone_redirect') == 'error') {
            Registry::getLog()->log(
                'returned from paypal through back|error url ',
                1,
                array(null, 'Cart', $this->context->cart->id)
            );
            $this->fcPayoneDeleteWorkOrderId();
            return false;
        }
        return true;
    }

    /**
     * Process paypal express payment
     * 1. setexpresscheckout redirect
     * 2. getexpresscheckout - create user, show order page
     * 3. preauth/auth request and order creation
     * @return bool
     */
    protected function fcPostProcessPayPalExpress()
    {
        if (!$this->fcPayoneCheckPayPalExpressRedirect()) {
            return false;
        }

        if (Tools::isSubmit('express_checkout_init')) {
            $this->fcPayoneDeleteWorkOrderId();
        }

        if (!Tools::isSubmit('payone_validate')) {
            $this->fcPayoneProcessPayPalExpressAction();
        } elseif (Tools::isSubmit('payone_validate') && $this->fcPayoneValidateOrder(true)) {
            $this->fcPayoneProcessPayPalExpressExecution();
        }
    }

    /**
     * Executes paypal express payment execution and
     * order creation
     *
     * @return bool
     */
    protected function fcPayoneProcessPayPalExpressExecution()
    {
        if (isset(\Context::getContext()->cookie->sFcPayoneWorkOrderId)) {
            $sWorkOrderId = \Context::getContext()->cookie->sFcPayoneWorkOrderId;
        } else {
            Registry::getErrorHandler()->setError(
                'order',
                'FC_PAYONE_ERROR_PAYPAL_EXPRESS_FAILED',
                true
            );
            $this->fcPayoneDeleteWorkOrderId();
            return false;
        }
        $blSuccess = false;
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $oRequest = new Request();
        if ($oRequest->processPayPalExpressExecution($this->context, $oSelectedPayment, $sWorkOrderId)) {
            $oResponse = new Response();
            $oResponse->setResponse($oRequest->getResponse());
            $blSuccess = $oResponse->processPayment();
        } else {
            Registry::getErrorHandler()->setError(
                'order',
                'FC_PAYONE_ERROR_PAYPAL_EXPRESS_REQUEST_FAILED',
                true
            );
        }
        if ($blSuccess) {
            $this->fcPayoneCreateOrder();
        } else {
            $this->fcPayoneDeleteWorkOrderId();
        }
    }

    /**
     * Process paypal express action
     * setexpresscheckout/getespresscheckoutdetals
     *
     */
    protected function fcPayoneProcessPayPalExpressAction()
    {
        if (isset(\Context::getContext()->cookie->sFcPayoneWorkOrderId)) {
            $sWorkOrderId = \Context::getContext()->cookie->sFcPayoneWorkOrderId;
        } else {
            $sWorkOrderId = null;
        }
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $oRequest = new Request();
        $blSuccess = false;
        if ($oRequest->processPayPalExpressCheckout($this->context, $oSelectedPayment, $sWorkOrderId)) {
            $oResponse = new Response();
            $oResponse->setResponse($oRequest->getResponse());
            $blSuccess = $oResponse->processPayPalExpress();
        } else {
            Registry::getErrorHandler()->setError(
                'order',
                'FC_PAYONE_ERROR_PAYPAL_EXPRESS_REQUEST_FAILED',
                true
            );
            Registry::getLog()->log('paypal express request failed', 3, array(null, 'Cart', $this->context->cart->id));
        }
        if (!$blSuccess) {
            $this->fcPayoneDeleteWorkOrderId();
        }
    }

    /**
     * Add payment specific stuff to base frontend
     * @param object $oPayment
     * @param object $oForm
     */
    protected function fcPayoneAddPaymentSpecifics($oPayment, $oForm)
    {
        $this->fcPayoneAddTermsCheck();
    }

    /**
     * Pre payment process
     *
     * @param $oSelectedPayment
     * @param boolean $blDeleteWorkerId
     *
     * Trigger work order id deleting
     *
     * @return bool
     */
    protected function fcPayonePaymentPreProcess($oSelectedPayment, $blDeleteWorkerId = true)
    {
        return parent::fcPayonePaymentPreProcess($oSelectedPayment, false);
    }
}
