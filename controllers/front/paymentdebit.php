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

use Payone\Forms\Frontend\Frontend as PayoneFrontendForm;
use Payone\Base\Registry;
use Payone\Request\Request;
use Payone\Response\Response;
use Payone\Base\Mandate;

require_once dirname(__FILE__) . '/payment.php';

class FcPayonePaymentDebitModuleFrontController extends FcPayonePaymentModuleFrontController
{
    /**
     * Add payment specific stuff to base frontend
     * @param object $oPayment
     * @param object $oForm
     */
    protected function fcPayoneAddPaymentSpecifics($oPayment, $oForm)
    {
        $blShowContinueButton = false;
        if (!$oForm->getMandate()) {
            $blShowContinueButton = true;
        }
        $this->context->smarty->assign('blFcPayoneShowContinueButton', $blShowContinueButton);
    }

    /**
     * Actions that are executed after the order is finished
     *
     * @param $oSelectedPayment
     * @param $oContext Context object from current order
     * @param $blError
     *
     * @return boolean
     */
    protected function fcPayonePostOrderAction($oSelectedPayment, $oContext, $blError = false)
    {
        $oMandate = new Mandate();
        if ($oMandate->setMandateFromContext($oContext)) {
            $oMandate->setOrderId($this->module->currentOrder);
            $oMandate->save();
        }
        $this->fcPayoneDeleteMandateCookie();
        parent::fcPayonePostOrderAction($oSelectedPayment, $oContext, $blError);
    }

    /**
     * Pre payment process
     *
     * @param $oSelectedPayment
     * @param boolean $blDeleteWorkerId
     *
     * @return bool
     */
    protected function fcPayonePaymentPreProcess($oSelectedPayment, $blDeleteWorkerId = false)
    {
        $blSuccess = parent::fcPayonePaymentPreProcess($oSelectedPayment, $blDeleteWorkerId);
        $oForm = new PayoneFrontendForm();
        $aFormData = $oForm->getFormData($oSelectedPayment);
        if (!isset($aFormData['mandate_loaded'])) {
            $this->fcPayoneDeleteMandateCookie();
        }
        return $blSuccess;
    }


    /**
     * Deletes mandate from cookie
     */
    protected function fcPayoneDeleteMandateCookie()
    {
        if (isset(Context::getContext()->cookie->sFcPayoneMandate)) {
            unset(Context::getContext()->cookie->sFcPayoneMandate);
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
        $oForm = new PayoneFrontendForm();
        $aFormData = $oForm->getFormData($oSelectedPayment);
        if ($aFormData && isset($aFormData['mandate_load'])) {
            $oRequest = new Request();
            $blProcessed = $oRequest->processMandateManage($this->context, $oSelectedPayment, $aFormData);
            if (!$blProcessed) {
                Registry::getErrorHandler()->setError(
                    'order',
                    'FC_PAYONE_ERROR_MANDATE_REQUEST_FAILED',
                    true
                );
                Registry::getLog()->log('mandate request failed', 3, array(null, 'Cart', $this->context->cart->id));
                return false;
            }
            $oResponse = new Response();
            $oResponse->setResponse($oRequest->getResponse());
            $oResponse->processMandateManage();
            return false;
        } elseif ($aFormData && (!isset($aFormData['mandate_accepted']) || $aFormData['mandate_accepted'] == '')) {
            Registry::getErrorHandler()->setError(
                'order',
                'FC_PAYONE_ERROR_MANDATE_NOT_ACCEPTED',
                true
            );
            return false;
        } elseif ($aFormData && isset($aFormData['mandate_accepted']) && $aFormData['mandate_accepted'] != '') {
            return true;
        } else {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_MANDATE_FAILED', true);
            Registry::getLog()->log('mandate failed', 3, array(null, 'Cart', $this->context->cart->id));
            $this->fcPayoneDeleteMandateCookie();
            return false;
        }
    }
}
