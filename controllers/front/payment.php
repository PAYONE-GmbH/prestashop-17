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
use Payone\Validation\Validation;
use Payone\Base\Order as PayoneOrder;
use Payone\Base\Reference;

class FcPayonePaymentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        Context::getContext()->cookie->sFcPayoneUserAgent = $_SERVER['HTTP_USER_AGENT'];

        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        Registry::getLog()->log(
            'process payment: ' . $oSelectedPayment->getId(),
            1,
            array(null, 'Cart', $this->context->cart->id)
        );
        $blSuccess = $this->fcPayonePaymentPreProcess($oSelectedPayment);
        if ($blSuccess) {
            if (Tools::isSubmit('payone_validate') && $this->fcPayoneValidateOrder()) {
                $sResult = $this->fcPayoneExecutePayment();
                if ($sResult == 'approved') {
                    $this->fcPayoneCreateOrder();
                }
            } elseif ($oSelectedPayment->isRedirectPayment() && Tools::getValue('payone_redirect') == 'success') {
                $blValid = $this->fcPayoneValidateOrder(true);
                $this->fcPayoneCreateOrder(!$blValid);
            }
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        $oSelectedPayment = Registry::getPayment()->getSelectedMainPaymentMethod();
        if ($oSelectedPayment->isGroupedPayment()) {
            $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        }
        $oFrontendForm = new PayoneFrontendForm();
        $oForm = $oFrontendForm->getFormObject($oSelectedPayment);
        $this->module->fcPayoneAddDefaultTemplateVars();
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'oFcPayonePayment' => $oSelectedPayment,
            'sPayoneSecureKey' => $this->module->secure_key,
            'sFcPayonePaymentForm' => $oForm->getForm(),
        ));
        $this->fcPayoneDisplayErrors();
        $this->fcPayoneAddPaymentSpecifics($oSelectedPayment, $oForm);
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return $this->setTemplate('module:fcpayone/views/templates/front/Payment/form.tpl');
        } else {
            return $this->setTemplate('Payment/form_legacy.tpl');
        }
    }


    /**
     * Display validation errors
     * and deletes them
     */
    protected function fcPayoneDisplayErrors()
    {
        $aErrors = Registry::getErrorHandler()->getErrors();
        if (is_array($aErrors) && count($aErrors) > 0) {
            $this->context->controller->errors = array_merge(
                $this->context->controller->errors,
                $aErrors
            );
        }
        Registry::getErrorHandler()->deleteErrors();
    }

    /**
     * Creates order
     * redirects to success page if everything was fine
     *
     * @param boolean $blError
     */
    protected function fcPayoneCreateOrder($blError = false)
    {
        $oCart = $this->context->cart;
        $oCustomer = new Customer($oCart->id_customer);
        $oCurrency = $this->context->currency;

        $sCartId = (int)$oCart->id;
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $sPaymentTitle = 'PAYONE - ' . $oSelectedPayment->getTitle();

        $oPayoneOrder = new PayoneOrder();
        $dTotal = (float)$oPayoneOrder->getFormattedRequestAmount(
            \Context::getContext()->cookie->iFcPayoneTransactionId
        );

        if (!$dTotal || $dTotal == 0) {
            Registry::getLog()->log('Could not fetch order amount from request', 3, array(null, 'Cart', $oCart->id));
            $blError = true;
        }

        $sState = Configuration::get('PS_OS_PREPARATION');
        if ($blError) {
            $sState = Configuration::get('PS_OS_ERROR');
        }
        Registry::getLog()->log('create order', 1, array(null, 'Cart', $oCart->id));

        $this->module->validateOrder(
            $sCartId,
            $sState,
            $dTotal,
            $sPaymentTitle,
            null,
            array('transaction_id' => \Context::getContext()->cookie->iFcPayoneTransactionId),
            (int)$oCurrency->id,
            false,
            $oCustomer->secure_key
        );
        $this->fcPayonePostOrderAction($oSelectedPayment, clone $this->context, $blError);

        $sUrl = 'index.php?controller=order-confirmation&id_cart=' . $sCartId .
            '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder .
            '&key=' . $oCustomer->secure_key . '&payone_payment=' . $oSelectedPayment->getId();
        Registry::getLog()->log('redirect to confirmation', 1, array(null, 'Cart', $oCart->id));
        Tools::redirect($sUrl);
    }

    /**
     * Initiates payment execution
     *
     * @return boolean
     */
    protected function fcPayoneExecutePayment()
    {
        Registry::getLog()->log('start payment execution', 1, array(null, 'Cart', $this->context->cart->id));
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $oForm = new PayoneFrontendForm();
        $oRequest = new Request();
        $blProcessed = $oRequest->processPayment(
            $this->context,
            $oSelectedPayment,
            $oForm->getFormData($oSelectedPayment)
        );
        if ($blProcessed) {
            $oResponse = new Response();
            $oResponse->setResponse($oRequest->getResponse());
            Registry::getLog()->log('process payment response', 1, array(null, 'Cart', $this->context->cart->id));
            return $oResponse->processPayment();
        } else {
            Registry::getErrorHandler()->setError(
                'order',
                'FC_PAYONE_ERROR_PAYMENT_EXECUTION_FAILED',
                true
            );
        }

        return false;
    }

    /**
     * Sets context for further checks
     *
     * @param int $id_cart
     * @param int $currency_special
     */
    protected function fcPayoneSetContext($id_cart, $currency_special)
    {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart((int)$id_cart);
        $this->context->customer = new Customer((int)$this->context->cart->id_customer);
        // The tax cart is loaded before the customer so re-cache the tax calculation method
        $this->context->cart->setTaxCalculationMethod();

        $this->context->language = new Language((int)$this->context->cart->id_lang);
        $this->context->shop = new Shop((int)$this->context->cart->id_shop);
        ShopUrl::resetMainDomainCache();
        $id_currency = $currency_special ? (int)$currency_special : (int)$this->context->cart->id_currency;
        $this->context->currency = new Currency((int)$id_currency, null, (int)$this->context->shop->id);
    }

    /**
     * Checks if module is active
     *
     * @return boolean
     */
    protected function fcPayoneIsModuleValid()
    {
        if ($this->module->active) {
            $blAuthorized = false;
            foreach (Module::getPaymentModules() as $aModule) {
                if ($aModule['name'] == 'fcpayone') {
                    $blAuthorized = true;
                    break;
                }
            }
            if (!$blAuthorized) {
                Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_MODULE_NOT_VALID');
            }

            return $blAuthorized;
        } else {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_MODULE_NOT_ACTIVE');
            return false;
        }
    }

    /**
     * Checks if orderstate is valid
     *
     * @return boolean
     */
    protected function fcPayoneIsOrderStateValid()
    {
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();
        $iOrderState = $oSelectedPayment->getPreCaptureState();
        $oOrderState = new OrderState((int)$iOrderState, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($oOrderState)) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDERSTATE_INVALID', true);
            return false;
        }
        return true;
    }

    /**
     * checks if order exists
     *
     * @return boolean
     */
    protected function fcPayoneCheckOrderExists()
    {
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false) {
            return true;
        }
        Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_ALREADY_EXISTS', true);
        return false;
    }

    /**
     * Checks secure key
     *
     * @param string $secure_key
     * @return boolean
     */
    protected function fcPayoneCheckSecureKey($secure_key)
    {
        if ($secure_key !== false && $secure_key != $this->context->cart->secure_key) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_SECUREKEY_INVALID', true);
            return false;
        }
        return true;
    }

    /**
     * Check cart rules
     *
     * @return boolean
     */
    protected function fcPayoneCheckCartRule()
    {
        // Make sure CartRule caches are empty
        CartRule::cleanCache();
        $cart_rules = $this->context->cart->getCartRules();
        foreach ($cart_rules as $cart_rule) {
            if (($rule = new CartRule((int)$cart_rule['obj']->id)) && Validate::isLoadedObject($rule)) {
                if ($error = $rule->checkValidity($this->context, true, true)) {
                    $this->context->cart->removeCartRule((int)$rule->id);
                    Registry::getErrorHandler()->setError(
                        'order',
                        'FC_PAYONE_ERROR_CARTRULE_INVALID',
                        true
                    );
                    if (isset($this->context->cookie) && isset($this->context->cookie->id_customer) &&
                        $this->context->cookie->id_customer && !empty($rule->code)
                    ) {
                        if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                            $sUrl = 'index.php?controller=order-opc&submitAddDiscount=1&discount_name=';
                        } else {
                            $sUrl = 'index.php?controller=order&submitAddDiscount=1&discount_name=';
                        }
                        Tools::redirect($sUrl . urlencode($rule->code));
                    } else {
                        if (isset($rule->name[(int)$this->context->cart->id_lang])) {
                            $rule_name = $rule->name[(int)$this->context->cart->id_lang];
                        } else {
                            $rule_name = $rule->code;
                        }
                        $error = sprintf(
                            Tools::displayError(
                                'CartRule ID %1s (%2s) used in this cart is not valid and has been withdrawn from cart'
                            ),
                            (int)$rule->id,
                            $rule_name
                        );
                        Registry::getLog()->log(
                            $error,
                            3,
                            array(
                                '0000002',
                                'Cart',
                                (int)$this->context->cart->id
                            )
                        );
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check delivery country
     *
     * @throws PrestaShopException
     */
    protected function fcPayoneCheckDeliveryCountry()
    {
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $context_country = $this->context->country;
        }

        // For each package, generate an order
        $delivery_option_list = $this->context->cart->getDeliveryOptionList();
        $package_list = $this->context->cart->getPackageList();
        $cart_delivery_option = $this->context->cart->getDeliveryOption();

        // If some delivery options are not defined, or not valid, use the first valid option
        foreach ($delivery_option_list as $id_address => $package) {
            if (!isset($cart_delivery_option[$id_address]) ||
                !array_key_exists($cart_delivery_option[$id_address], $package)
            ) {
                foreach ($package as $key => $val) {
                    $cart_delivery_option[$id_address] = $key;
                    break;
                }
            }
        }

        foreach ($cart_delivery_option as $id_address => $key_carriers) {
            foreach ($delivery_option_list[$id_address][$key_carriers]['carrier_list'] as $id_carrier => $data) {
                foreach ($data['package_list'] as $id_package) {
                    // Rewrite the id_warehouse
                    $package_list[$id_address][$id_package]['id_warehouse'] =
                        (int)$this->context->cart->getPackageIdWarehouse(
                            $package_list[$id_address][$id_package],
                            (int)$id_carrier
                        );
                    $package_list[$id_address][$id_package]['id_carrier'] = $id_carrier;
                }
            }
        }

        foreach ($package_list as $id_address => $packageByAddress) {
            foreach ($packageByAddress as $id_package => $package) {
                if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                    $address = new Address((int)$id_address);
                    $this->context->country = new Country(
                        (int)$address->id_country,
                        (int)$this->context->cart->id_lang
                    );
                    if (!$this->context->country->active) {
                        Registry::getErrorHandler()->setError(
                            'order',
                            'FC_PAYONE_ERROR_COUNTRY_INVALID',
                            true
                        );
                        return false;
                    }
                }
            }
        }

        // The country can only change if the address used for the calculation is the delivery address,
        // and if multi-shipping is activated
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $this->context->country = $context_country;
        }

        if (!$this->context->country->active) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_COUNTRY_INVALID', true);
            return false;
        }
        return true;
    }

    /**
     * Pre validateOrder validation
     *
     * @param boolean $blAfterRedirect
     *
     * @return boolean
     */
    protected function fcPayoneValidateOrder($blAfterRedirect = false)
    {
        if (!$this->fcPayoneIsModuleValid()) {
            return false;
        }
        $oValidation = new Validation();
        $oValidation->setAfterRedirect($blAfterRedirect);
        $sToken = Tools::getToken(false);
        if ($oValidation->validateCheckout($this->context, $sToken) == false) {
            Registry::getLog()->log(
                'base checkout validation failed',
                3,
                array(null, 'Cart', $this->context->cart->id)
            );
            return false;
        }

        $oCart = $this->context->cart;
        $oCustomer = new Customer($oCart->id_customer);
        $oCurrency = $this->context->currency;
        $this->fcPayoneSetContext((int)$oCart->id, (int)$oCurrency->id);
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();

        if ($oSelectedPayment && !$this->fcPayonePaymentSpecificChecks($oSelectedPayment, $blAfterRedirect)) {
            Registry::getLog()->log('payment validation failed', 3, array(null, 'Cart', $this->context->cart->id));
            return false;
        }
        if ($oValidation->validatePayment($oSelectedPayment) &&
            $this->fcPayoneCheckOrderExists() &&
            $this->fcPayoneCheckSecureKey($oCustomer->secure_key) &&
            $this->fcPayoneCheckCartRule() &&
            $this->fcPayoneCheckDeliveryCountry()
        ) {
            return true;
        }
        Registry::getLog()->log('checkout validation failed', 3, array(null, 'Cart', $this->context->cart->id));
        return false;
    }

    /**
     * Deletes transactionid from cookie
     */
    protected function fcPayoneDeleteTxIdCookie()
    {
        if (isset(Context::getContext()->cookie->iFcPayoneTransactionId)) {
            unset(Context::getContext()->cookie->iFcPayoneTransactionId);
        }
    }

    /**
     * Updates transaction with order id
     * and deletes transactionid cookie
     */
    protected function fcPayoneHandleTransaction()
    {
        $iTxId = \Context::getContext()->cookie->iFcPayoneTransactionId;
        $oSelectedPayment = Registry::getPayment()->getSelectedPaymentMethod();

        $sReference = '';
        if ($iTxId) {
            //get first reference and set txid(dosnt change anymore)
            $sReference = $this->fcPayoneGetReferenceByTxId($iTxId);
            if ($sReference) {
                Reference::updateTxId($sReference, $iTxId);
            }
        }

        $oOrder = new PayoneOrder();
        $oOrder->setOrderId($this->module->currentOrder);
        $oOrder->setOrderReference($this->module->currentOrderReference);
        $oOrder->setReference($sReference);
        $oOrder->setPaymentId($oSelectedPayment->getId());
        $oOrder->setTxId($iTxId);
        $oOrder->setRequestType($oSelectedPayment->getRequestType());
        $oOrder->setMode($oSelectedPayment->getMode());
        $iUserId = Registry::getUser()->getPayoneUserIdByCustomerId(\Context::getContext()->cart->id_customer);
        $oOrder->setUserId($iUserId);
        $oOrder->save();

        $this->fcPayoneDeleteTxIdCookie();
    }


    /**
     * Returns reference by txid
     *
     * @param $sTxId
     * @return mixed
     */
    protected function fcPayoneGetReferenceByTxId($sTxId)
    {
        if ($sTxId) {
            $sCleanTxId = (int)\pSQL($sTxId);
            $sQ = "select reference from " . _DB_PREFIX_ . Request::getTable() .
                " where txid = '{$sCleanTxId}' order by date asc";
            $sReference = \Db::getInstance()->getValue($sQ);
            if ($sReference) {
                return $sReference;
            }
        }
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
        $this->fcPayoneDeleteWorkOrderId();
        if ($blError) {
            $aErrors = Registry::getErrorHandler()->getErrors();
            if (is_array($aErrors) && count($aErrors) > 0) {
                foreach ($aErrors as $sError) {
                    Registry::getLog()->log(
                        $this->module->currentOrderReference . ' - ' . $sError,
                        3,
                        array(
                            null,
                            'Cart',
                            (int)$this->context->cart->id
                        )
                    );
                }
            }
        }

        $this->fcPayoneHandleTransaction();
        unset($oContext);
    }

    /**
     * Add terms check to payment
     */
    protected function fcPayoneAddTermsCheck()
    {
        $cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
        $link_conditions = $this->context->link->getCMSLink(
            $cms,
            $cms->link_rewrite,
            Configuration::get('PS_SSL_ENABLED')
        );
        if (!strpos($link_conditions, '?')) {
            $link_conditions .= '?content_only=1';
        } else {
            $link_conditions .= '&content_only=1';
        }
        $this->context->smarty->assign(array(
            'sFcPayoneConditionLink' => $link_conditions,
            'iFcPayoneConditionCmsId' => (int)Configuration::get('PS_CONDITIONS_CMS_ID'),
            'iFcPayoneConditions' => (int)Configuration::get('PS_CONDITIONS'),
        ));
        $this->context->controller->addJquery();
        $this->context->controller->addJS(
            Registry::getHelper()->getModulePath() . 'views/js/frontend/fcpayoneterms.js'
        );
    }

    /**
     * Deletes workorder id
     */
    protected function fcPayoneDeleteWorkOrderId()
    {
        if (isset(\Context::getContext()->cookie->sFcPayoneWorkOrderId)) {
            unset(\Context::getContext()->cookie->sFcPayoneWorkOrderId);
        }
    }

    /**
     * Pre payment process
     *
     * @param $oSelectedPayment
     * @param boolean $blDeleteWorkerId
     *
     * @return bool
     */
    protected function fcPayonePaymentPreProcess($oSelectedPayment, $blDeleteWorkerId = true)
    {
        if (\Tools::getValue('payone_redirect_order')) {
            if (\Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                \Tools::redirect('index.php?controller=order-opc');
            }
            \Tools::redirect('index.php?controller=order');
        }

        if ($blDeleteWorkerId) {
            $this->fcPayoneDeleteWorkOrderId();
        }

        return true;
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
        return true;
    }

    /**
     * Add payment specific stuff to base frontend
     * @param object $oPayment
     * @param object $oForm
     */
    protected function fcPayoneAddPaymentSpecifics($oPayment, $oForm)
    {
    }
}
