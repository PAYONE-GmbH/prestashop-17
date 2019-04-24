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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'fcpayone/vendor/autoload.php';

class FcPayone extends \PaymentModule
{
    /**
     * Module conctructer sets name and basic functions
     */
    public function __construct()
    {
        $this->name = 'fcpayone';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        if (!defined('_FCPAYONE_VERSION_')) {
            define('_FCPAYONE_VERSION_', $this->version);
        }
        $this->author = 'patworx multimedia GmbH';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = 'd19ef9306567fb99c3b4e9aace0ef2b3';
        parent::__construct();
        $this->secure_key = \Tools::encrypt($this->name);
        $oTranslator = \Payone\Base\Registry::getTranslator();
        $this->displayName = 'PAYONE GmbH Connector';
        $this->description = 'PAYONE GmbH Connector for Prestashop';
        $this->confirmUninstall = $oTranslator->translate('FC_PAYONE_BACKEND_CONFIRM_UNINSTALL');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Returns helper
     *
     * @return \Payone\Helper\Helper
     */
    protected function fcGetPayoneHelper()
    {
        return \Payone\Base\Registry::getHelper();
    }

    /**
     * Ident from form submit
     * @var string
     */
    protected $sFcPayoneSubmitIdent = null;

    /**
     * Triggers install and creates database
     *
     * @return boolean
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = \Payone\Base\Registry::getTranslator()->translate('FC_PAYONE_ERROR_CURL_NEEDED');
            return false;
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayAdminOrderLeft') &&
            $this->registerHook('displayShoppingCart') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->install16Hook() &&
            $this->fcPayoneCreateTables() &&
            $this->fcPayoneAddDefaultConfiguration();
    }

    protected function install16Hook()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<') || Tools::substr(_PS_VERSION_, 0, 3) == '1.6') {
            return $this->registerHook('payment') &&
                $this->registerHook('displayPaymentEU');
        }
        return true;
    }

    /**
     * Triggers uninstall and removes database and config entrys
     *
     * @return boolean
     */
    public function uninstall()
    {
        if (!$this->fcPayoneDeleteTables()) {
            return false;
        }

        $oBackendForms = new \Payone\Forms\Backend\Backend;
        $aBackendForms = $oBackendForms->getConfigurationForms();
        $aConfigFields = array();
        foreach ($aBackendForms as $aFormTypes) {
            foreach ($aFormTypes as $oForm) {
                $aConfigFields = array_merge($aConfigFields, $oForm->getFields());
            }
        }

        $aConfigFields = array_merge($aConfigFields, $this->fcPayoneGetConfigurationsToRemove());

        foreach ($aConfigFields as $sConfigKey) {
            //delete config params
            \Configuration::deleteByName(str_replace('[]', '', $sConfigKey));
        }

        return parent::uninstall();
    }

    /**
     * Returns array with payone tables
     *
     * @return array
     */
    protected function fcPayoneGetTables()
    {
        return array(
            '###TABLE_REQUEST###' => _DB_PREFIX_ . \Payone\Request\Request::getTable(),
            '###TABLE_TRANSACTION###' => _DB_PREFIX_ . \Payone\Base\Transaction::getTable(),
            '###TABLE_USER###' => _DB_PREFIX_ . \Payone\Base\User::getTable(),
            '###TABLE_MANDATE###' => _DB_PREFIX_ . \Payone\Base\Mandate::getTable(),
            '###TABLE_REFERENCE###' => _DB_PREFIX_ . \Payone\Base\Reference::getTable(),
            '###TABLE_ORDER###' => _DB_PREFIX_ . \Payone\Base\Order::getTable(),
        );
    }

    /**
     * Creates payone tables
     *
     * @return boolean
     */
    protected function fcPayoneCreateTables()
    {
        $sRawQ = \Tools::file_get_contents(\Payone\Base\Registry::getHelper()->getModulePath() . '/sql/install.sql');
        $aTables = $this->fcPayoneGetTables();
        $aPattern = array_keys($aTables);
        $aPattern[] = '###TABLE_ENGINE###';
        $aReplace = $aTables;
        $aReplace[] = _MYSQL_ENGINE_;

        $sQ = str_replace($aPattern, $aReplace, $sRawQ);
        if (\Db::getInstance()->execute($sQ) == false) {
            return false;
        }
        return true;
    }

    /**
     * Drops payone tables on uninstall
     *
     * @return boolean
     */
    protected function fcPayoneDeleteTables()
    {
        $aTables = $this->fcPayoneGetTables();
        $sQ = '';
        foreach ($aTables as $sTable) {
            $sQ .= 'DROP TABLE IF EXISTS `' . $sTable . '`;';
        }
        return (bool)\Db::getInstance()->execute($sQ);
    }

    /**
     * Adds default configuration values
     */
    protected function fcPayoneAddDefaultConfiguration()
    {
        //special value, is not set with normal generic configuration value handling
        \Configuration::updateValue('FC_PAYONE_PAYPAL_EXPRESS_IMG_1', 'paypal_express_1.png');

        //set default
        \Configuration::updateValue('FC_PAYONE_PAYMENT_SHOW_IBANBIC_ONLINETRANSFER_SOFORTBANKING', '1');
        return true;
    }

    /**
     * Returns configurations to delete
     */
    protected function fcPayoneGetConfigurationsToRemove()
    {
        return array('FC_PAYONE_PAYPAL_EXPRESS_IMG_1');
    }

    /**
     * Load the configuration form
     *
     * @return string
     */
    public function getContent()
    {
        $this->fcPayoneAddDefaultTemplateVars();
        $aFormLists = $this->fcPayoneGetConfigurationForms();
        $this->fcPayonePostProcess($aFormLists);
        $aForms = array();
        $sFormSubmitIdent = $this->fcPayoneGetSubmitIdent();
        $sActiveFormType = 'general';
        foreach ($aFormLists as $sFormType => $aFormList) {
            $i = 0;
            foreach ($aFormList as $oForm) {
                $blActive = false;
                if ($sFormSubmitIdent && $sFormSubmitIdent == $oForm->getSubmitName()) {
                    $blActive = true;
                    $sActiveFormType = $sFormType;
                } elseif (!$sFormSubmitIdent && $sFormType == 'general' && $i == 0) {
                    $blActive = true;
                }
                $aForms[$sFormType][] = array(
                    'title' => $oForm->getTitle(),
                    'content' => $this->fcPayoneBuildForm(
                        $oForm->getForm(),
                        $oForm->getValues($oForm->getFields()),
                        $oForm->getSubmitName()
                    ),
                    'active' => $blActive,
                );
                $i++;
            }
        }

        $this->fcPayoneAddRegisterButton();

        $this->context->smarty->assign(
            'sFcPayoneLogo',
            $this->fcGetPayoneHelper()->getModuleUrl() . 'views/img/PAYONE_Logo_RGB.jpg'
        );
        $this->context->controller->addCSS(
            $this->fcGetPayoneHelper()->getModulePath() . 'views/css/backend/payone.css',
            'all'
        );
        $sContent = $this->context->smarty->fetch(
            $this->fcGetPayoneHelper()->getModulePath() . 'views/templates/admin/info.tpl'
        );
        $this->context->smarty->assign('sFcPayoneActiveFormType', $sActiveFormType);
        $this->context->smarty->assign('aFcPayoneForms', $aForms);
        $sContent .= $this->context->smarty->fetch(
            $this->fcGetPayoneHelper()->getModulePath() . 'views/templates/admin/configuration.tpl'
        );
        return $sContent;
    }

    /**
     * Adds register button to info template if needed
     *
     */
    protected function fcPayoneAddRegisterButton()
    {
        if (!\Configuration::get('FC_PAYONE_CONNECTION_MERCHANTID') ||
            !\Configuration::get('FC_PAYONE_CONNECTION_PORTALID') ||
            !\Configuration::get('FC_PAYONE_CONNECTION_PORTALKEY')
        ) {
            if ($this->context->language->iso_code == 'de') {
                $sCallToActionUrl = 'https://www.payone.com/go/payment/';
            } else {
                $sCallToActionUrl = 'https://www.payone.com/go/payment/en/';
            }
            $this->context->smarty->assign(
                'sFcPayoneButtonUrl',
                $sCallToActionUrl
            );
        }
    }

    /**
     * Returns configuration forms
     *
     * @return array
     */
    protected function fcPayoneGetConfigurationForms()
    {
        $oForm = new \Payone\Forms\Backend\Backend();
        $oForm->setContext($this->context);
        $oForm->setModule($this);
        return $oForm->getConfigurationForms();
    }

    /**
     * Build forms
     *
     * @param $aForm
     * @param $aValues
     * @param $sSubmitName
     * @return mixed
     */
    protected function fcPayoneBuildForm($aForm, $aValues, $sSubmitName)
    {
        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = $sSubmitName;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = \Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $aValues, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(
            array($aForm)
        );
    }

    /**
     * Save form data.
     *
     * @param $aForms
     * @return void
     */
    protected function fcPayonePostProcess($aForms)
    {
        foreach ($aForms as $aFormTypes) {
            foreach ($aFormTypes as $oForm) {
                if (((bool)Tools::isSubmit($oForm->getSubmitName())) == true) {
                    $this->fcPayoneSetSubmitIdent($oForm->getSubmitName());
                    $aFields = $oForm->getFields();
                    foreach ($aFields as $sKey) {
                        $oForm->handleUpdate($sKey);
                    }
                }
            }
        }
    }

    /**
     * Returns form submit Ident
     *
     * @return string
     */
    protected function fcPayoneGetSubmitIdent()
    {
        return $this->sFcPayoneSubmitIdent;
    }

    /**
     * Sets form submit Ident for active flag
     *
     * @param string
     */
    protected function fcPayoneSetSubmitIdent($sIdent)
    {
        $this->sFcPayoneSubmitIdent = $sIdent;
    }

    /**
     * Diaplay the payone panel in admin order area to change the status...
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminOrderLeft($params)
    {
        $oOrder = new Order($params['id_order']);
        if ($oOrder->module !== $this->name) {
            \Payone\Base\Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_FAIL');
            return;
        }
        $oOrderForm = new \Payone\Forms\Backend\Order;
        $oOrderForm->setModule($this);
        $oOrderForm->setOrder($oOrder);
        $oOrderForm->setContext($this->context);
        return $oOrderForm->getForm();
    }

    /**
     * Checks if payment can be hoocked
     *
     * @param array $params
     * @return boolean
     */
    protected function fcPayoneCanHookPayment($params)
    {
        if (!$this->active || !$params || !isset($params['cart'])) {
            return false;
        }

        $oValidation = new \Payone\Validation\Validation;
        $oCart = $params['cart'];

        $oCurrency = new \Currency($oCart->id_currency);
        $aCurrenciesModule = $this->getCurrency($oCart->id_currency);
        if (!$oValidation->validateCurrency($oCurrency, $aCurrenciesModule)) {
            return false;
        }
        return true;
    }

    /**
     * Returns payment methods that are valid to display for the current user
     *
     * @return array
     */
    protected function fcPayoneGetValidUserPaymentMethods()
    {
        //reset worker order id
        if (isset(\Context::getContext()->cookie->sFcPayoneWorkOrderId)) {
            unset(\Context::getContext()->cookie->sFcPayoneWorkOrderId);
        }
        if (isset(Context::getContext()->cookie->iFcPayoneTransactionId)) {
            unset(Context::getContext()->cookie->iFcPayoneTransactionId);
        }

        $aNotVisibleInList = array('wallet_paypal_express');
        $aValidPaymentMethods = array();
        $aPaymentMethods = \Payone\Base\Registry::getPayment()->getPaymentMethods();
        foreach ($aPaymentMethods as $sKey => $oPayment) {
            if (!$oPayment->isGroupedPayment() && !in_array($oPayment->getId(), $aNotVisibleInList) &&
                (($oPayment->hasSubPayments() && count($oPayment->getValidSubPayments()) > 0) ||
                    (!$oPayment->hasSubPayments() && $oPayment->isValidForCheckout()))
            ) {
                $aValidPaymentMethods[$sKey] = $oPayment;
            }
        }

        foreach ($aPaymentMethods as $sKey => $oPayment) {
            if ($oPayment->isGroupedPayment()) {
                $aSubPayments = $oPayment->getValidSubPayments();
                foreach ($aSubPayments as $sSubKey => $oSubPayment) {
                    if (!in_array($oSubPayment->getId(), $aNotVisibleInList)) {
                        $aValidPaymentMethods[$sSubKey] = $oSubPayment;
                    }
                }
            }
        }

        return $aValidPaymentMethods;
    }

    /**
     * Regular Payment Option in PS1.7.x
     *
     * @param $params
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            return false;
        }
        if (!$this->active) {
            return array();
        }
        if (!$this->fcPayoneCanHookPayment($params)) {
            return array();
        }
        \Payone\Base\Registry::getLog()->log('hook payment', 1, array(null, 'Payment', $params['cart']->id));
        $this->fcPayoneAddDefaultTemplateVars();

        $payment_options = array();
        $payment_options_raw = $this->fcPayoneGetPaymentsForTemplate();
        foreach ($payment_options_raw as $po) {
            $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $newOption->setCallToActionText($po['cta_text'])
                ->setModuleName($this->name)
                ->setLogo($po['logo'])
                ->setAction($po['action']);
            $payment_options[] = $newOption;
        }
        return $payment_options;
    }

    /**
     * Hook payment to show paymethod
     * checks if currency is allowed
     *
     * @param array $params
     * @return boolean|string
     */
    public function hookPayment($params)
    {
        if (!$this->fcPayoneCanHookPayment($params)) {
            return false;
        }
        \Payone\Base\Registry::getLog()->log('hook payment', 1, array(null, 'Payment', $params['cart']->id));
        $this->fcPayoneAddDefaultTemplateVars();
        $aPaymentMethods = $this->fcPayoneGetValidUserPaymentMethods();
        if (is_array($aPaymentMethods) && count($aPaymentMethods) > 0) {
            $this->smarty->assign('aFcPayonePaymentMethods', $aPaymentMethods);
        } else {
            return false;
        }

        return $this->display(
            $this->fcGetPayoneHelper()->getModulePath(),
            'views/templates/hook/front/payment_selection.tpl'
        );
    }

    /**
     * Hook payment to show paymethod
     * checks if currency is allowed
     * EU compat mode
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayPaymentEU($params)
    {
        if (!$this->fcPayoneCanHookPayment($params)) {
            return;
        }
        \Payone\Base\Registry::getLog()->log('hook payment', 1, array(null, 'Payment', $params['cart']->id));
        $this->fcPayoneAddDefaultTemplateVars();

        return $this->fcPayoneGetPaymentsForTemplate();
    }

    /**
     * Returns array with payment methdos shown in template
     *
     * @return array
     */
    protected function fcPayoneGetPaymentsForTemplate()
    {
        $aReturn = array();
        $aPaymentMethods = $this->fcPayoneGetValidUserPaymentMethods();
        if (!is_array($aPaymentMethods) || count($aPaymentMethods) == 0) {
            return $aReturn;
        }
        foreach ($aPaymentMethods as $oPayment) {
            if ($oPayment->isGroupedPayment()) {
                $aControllerParams = array(
                    'payone_payment' => \Tools::strtolower(\Tools::strtoupper($oPayment->getParentId())),
                    'payone_payment_sub' => \Tools::strtolower($oPayment->getId())
                );
            } else {
                $aControllerParams = array(
                    'payone_payment' => \Tools::strtolower($oPayment->getId()),
                );
            }

            $aReturn[] = array(
                'cta_text' => $oPayment->getTitle(),
                'logo' => \Media::getMediaPath(
                    $this->fcGetPayoneHelper()->getModulePath() . '/views/image/paymentmethods/' . $oPayment->getImage()
                ),
                'action' => $this->context->link->getModuleLink(
                    $this->name,
                    $oPayment->getController(),
                    $aControllerParams,
                    true
                )
            );
        }

        return $aReturn;
    }

    /**
     * Adds mandate download link to template
     *
     * @param $iOrderId
     * @param $iCustomerId
     */
    public function fcPayoneAddMandateDownloadLink($iOrderId, $iCustomerId)
    {
        $sTable = _DB_PREFIX_ . \Payone\Base\Mandate::getTable();
        $iCleanOrderId = (int)\pSQL($iOrderId);
        $sQ = "select mandate_identifier from $sTable where id_order = '{$iCleanOrderId}'";
        $sMandateIdentifier = Db::getInstance()->getValue($sQ);

        $this->context->smarty->assign(
            array(
                'sFcPayoneDownloadLink' => $this->context->link->getModuleLink(
                    $this->name,
                    'download',
                    array(
                        'payone_orderid' => $iOrderId,
                        'payone_ident' => $sMandateIdentifier,
                        'payone_customer' => $iCustomerId
                    )
                ),
            )
        );
    }

    /**
     * frontend order overview after payment and validation
     *
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        if (isset($params['objOrder'])) {
            $oOrder = $params['objOrder'];
        } elseif (isset($params['order'])) {
            $oOrder = $params['order'];
        } else {
            return;
        }
        \Payone\Base\Registry::getLog()->log('hook payment return', 1, array(null, 'Order', $oOrder->id));

        $this->fcPayoneCleanup();
        if (\Tools::getValue('payone_payment') == 'debit') {
            $this->fcPayoneAddMandateDownloadLink($oOrder->id, $oOrder->id_customer);
        }
        $blError = false;
        if ($oOrder->getCurrentOrderState()->id == Configuration::get('PS_OS_ERROR')) {
            $blError = true;
        }

        $this->smarty->assign(
            array(
                'iFcPayoneOrderId' => $oOrder->id,
                'sFcPayoneOrderReference' => $oOrder->reference,
                'blFcPayoneError' => $blError
            )
        );
        $this->fcPayoneAddDefaultTemplateVars();
        return $this->display(
            $this->fcGetPayoneHelper()->getModulePath(),
            'views/templates/hook/front/confirmation.tpl'
        );
    }

    /**
     * Cleanup after order is completed
     */
    protected function fcPayoneCleanup()
    {
        if (isset(\Context::getContext()->cookie->sFcPayoneMandate)) {
            unset(\Context::getContext()->cookie->sFcPayoneMandate);
        }
        \Payone\Base\Registry::getErrorHandler()->deleteErrors();
    }

    /**
     * Add paypal express template to hook
     *
     * @param array
     *
     * @return void|string
     */
    public function hookDisplayShoppingCart($params)
    {
        $sCartId = null;
        if ($params && isset($params['cart']) && $params['cart']->id) {
            $sCartId = $params['cart']->id;
        }

        \Payone\Base\Registry::getLog()->log('hook display shoppingcart', 1, array(null, 'Cart', $sCartId));
        $oPayPalExpress = $this->getOPayPalExpress();

        if (isset(\Context::getContext()->cookie->sFcPayoneWorkOrderId)) {
            unset(\Context::getContext()->cookie->sFcPayoneWorkOrderId);
        }

        if (!$oPayPalExpress) {
            return;
        }

        $this->fcPayoneAddDefaultTemplateVars();

        $this->smarty->assign(
            array(
                'oFcPayPalExpress' => $oPayPalExpress,
            )
        );
        return $this->display(
            $this->fcGetPayoneHelper()->getModulePath(),
            $oPayPalExpress->getPayPalExpressTemplate('cart')
        );
    }

    /**
     * Add paypal express CSS to header hook
     *
     * @param array
     *
     * @return void|string
     */
    public function hookDisplayHeader($params)
    {
        $oPayPalExpress = $this->getOPayPalExpress();
        if (!$oPayPalExpress) {
            return;
        }
        $this->context->controller->addCSS(
            $this->fcGetPayoneHelper()->getModulePath() . 'views/css/frontend/paypal_express_btn_cart.css',
            'all'
        );
    }

    /**
     * Helper to get possible PayPalExpress Payment
     *
     * @return object $oPayPalExpress
     */
    protected function getOPayPalExpress()
    {
        $aPaymentMethods = \Payone\Base\Registry::getPayment()->getPaymentMethods();
        $oPayPalExpress = null;
        if (is_array($aPaymentMethods) && count($aPaymentMethods) > 0) {
            foreach ($aPaymentMethods as $oPayment) {
                if ($oPayment->getId() == 'wallet') {
                    $aSubPayments = $oPayment->getValidSubPayments();
                    if ($aSubPayments && isset($aSubPayments['wallet_paypal_express'])) {
                        $oPayPalExpress = $aSubPayments['wallet_paypal_express'];
                    }
                }
            }
        }
        return $oPayPalExpress;
    }

    /**
     * Adds default vars to template like url, path....
     *
     * @param object $oContent
     */
    public function fcPayoneAddDefaultTemplateVars($oContent = null)
    {
        if (!$oContent) {
            $oContent = $this->context;
        }
        $oContent->smarty->assign(array(
            'sFcPayoneModulePath' => $this->fcGetPayoneHelper()->getModulePath(),
            'sFcPayoneModuleUrl' => $this->fcGetPayoneHelper()->getModuleUrl(),
            'sFcPayoneModuleId' => $this->name,
            'oFcPayoneTranslator' => \Payone\Base\Registry::getTranslator(),
        ));
    }

    /**
     * Displays clearing-Data at PDF invoices if needed
     *
     * @param object $params
     */
    public function hookDisplayPDFInvoice($params)
    {
        $oOrder = new Order((int)$params['object']->id_order);
        if ($oOrder->module !== $this->name) {
            return;
        }
        $this->fcPayoneAddDefaultTemplateVars();
        $oOrderForm = new \Payone\Forms\Backend\Order;
        $oOrderForm->setModule($this);
        $oOrderForm->setOrder($oOrder);
        $oOrderForm->setContext($this->context);
        $oPayoneOrder = new \Payone\Base\Order();
        $aOrderData = $oPayoneOrder->getOrderData($oOrder->id);
        $hasClearingData = $oOrderForm->addLastRequestWithClearingData($aOrderData['txid']);
        if ($hasClearingData) {
            $this->context->smarty->assign('usage', $aOrderData['txid']);
            return $this->display(
                $this->fcGetPayoneHelper()->getModulePath(),
                'views/templates/hook/admin/displayPDFInvoice.tpl'
            );
        }
    }
}
