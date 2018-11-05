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

namespace Payone\Payment\Methods;

use Payone\Base\Registry;
use Payone\Validation\Validation;

class Base
{
    /**
     * Shortcut for request type
     */
    const REQUEST_AUTH = 'authorization';

    /**
     * Shortcut for request type
     */
    const REQUEST_PREAUTH = 'preauthorization';

    /**
     * ID
     *
     * @var string
     */
    protected $sId = null;

    /**
     * ID of parent payment
     *
     * @var string
     */
    protected $sParentId = null;

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sClearingType = null;

    /**
     * Sub clearing type
     * eg. onlinetransfer
     *
     * @var string
     */
    protected $sSubClearingType = null;

    /**
     * Payment need redirect urls
     *
     * @var boolean
     */
    protected $blIsRedirectPayment = false;

    /**
     * Payment paramerter
     * eg. pseudocardpan, shippingprovider...
     *
     * @var array
     */
    protected $aParameter = array();

    /**
     * PaymentImage
     *
     * @var string
     */
    protected $sImage = null;

    /**
     * Base template path for payments
     *
     * @var string
     */
    protected $sTemplateBasePath = 'views/templates/front/Payment/Methods/';

    /**
     * Template file path
     *
     * @var string
     */
    protected $sTemplate = 'default.tpl';

    /**
     * Is payment active
     *
     * @var boolean
     */
    protected $blActive = false;

    /**
     * Array with whitelist country iso codes
     *
     * @var array
     */
    protected $aCountryWhitelist = array();

    /**
     * Array with blacklist country iso codes
     *
     * @var array
     */
    protected $aCountryBlacklist = array();

    /**
     * Act Request mode
     *
     * @var string
     */
    protected $sActRequestType = null;

    /**
     * Available request type
     *
     * @var string
     */
    protected $aRequestTypes = array();

    /**
     * Marker for sub payments
     *
     * @var boolean
     */
    protected $blHasSubPayments = false;

    /**
     * array withs ubpayments
     *
     * @var array
     */
    protected $aSubPayments = null;

    /**
     * Operation mode
     *
     * @var string
     */
    protected $sMode = null;

    /**
     * Payment frontend controller
     *
     * @var string
     */
    protected $sController = 'payment';

    /**
     * Account settlement possiblity
     *
     * @var bool
     */
    protected $blAllowAccountSettlement = false;

    /**
     * True if bank data is required for debit request
     *
     * @var bool
     */
    protected $blNeedBankDataForDebit = false;

    /**
     * Logical grouping flag for non real subpayments
     * like wallet with paypal, amazon..
     * @var bool
     */
    protected $blIsGroupedPayment = false;

    /**
     * Disable amount input for capture/refund
     * eg. secure invoice
     *
     * @var bool
     */
    protected $blDisableAmountInput = false;

    /**
     * Add items to capture request
     *
     * @var bool
     */
    protected $blIsItemsRequiredInCaptureRequest = false;

    /**
     * Add items to debit/refund request
     *
     * @var bool
     */
    protected $blIsItemsRequiredInDebitRequest = false;

    /**
     * inits params
     *
     */
    public function __construct()
    {
        $sIdent = \Tools::strtoupper($this->getId());
        $this->setActive((bool)\Configuration::get('FC_PAYONE_PAYMENT_ACTIVE_' . $sIdent));
        $this->setRequestType(\Configuration::get('FC_PAYONE_PAYMENT_REQUEST_TYPE_' . $sIdent));
        $sMode = \Configuration::get('FC_PAYONE_PAYMENT_MODE_LIVE_' . $sIdent) ? 'live' : 'test';
        $this->setMode($sMode);
    }

    /**
     * Returns id
     *
     * @param boolean $blUpperCase in case id need to be uppercase
     * @return string
     */
    public function getId($blUpperCase = false)
    {
        if ($blUpperCase) {
            return \Tools::strtoupper($this->sId);
        }
        return $this->sId;
    }

    /**
     * Returns parent id
     *
     * @param boolean $blUpperCase in case id need to be uppercase
     * @return string
     */
    public function getParentId($blUpperCase = false)
    {
        if ($blUpperCase) {
            return \Tools::strtoupper($this->sParentId);
        }
        return $this->sParentId;
    }

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle()
    {
        return Registry::getTranslator()->translate('FC_PAYONE_PAYMENT_TITLE_' . $this->getId(true));
    }

    /**
     * Returns desciption
     *
     * @return string
     */
    public function getDescription()
    {
        $sIdent = 'FC_PAYONE_PAYMENT_DESC_' . $this->getId(true);
        $sDesc = Registry::getTranslator()->translate($sIdent);
        if ($sIdent != $sDesc) {
            return $sDesc;
        }
    }

    /**
     * Returns clearing type
     *
     * @return string
     */
    public function getClearingType()
    {
        return $this->sClearingType;
    }

    /**
     * Returns sub clearing type
     *
     * @return string
     */
    public function getSubClearingType()
    {
        return $this->sSubClearingType;
    }

    /**
     * Returns true if redirect urls are needed
     *
     * @return boolean
     */
    public function isRedirectPayment()
    {
        return $this->blIsRedirectPayment;
    }

    /**
     * Returns payment parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameter;
    }

    /**
     * Returns image file name
     *
     * @return string
     */
    public function getImage()
    {
        return $this->sImage;
    }

    /**
     * Returns template base path
     *
     * @return string
     */
    public function getTemplateBasePath()
    {
        return $this->sTemplateBasePath;
    }

    /**
     * Returns template path
     *
     * @return string
     */
    public function getTemplate()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            return str_replace(".tpl", "_legacy.tpl", $this->sTemplate);
        } else {
            return $this->sTemplate;
        }
    }

    /**
     * Returns full template path
     *
     * @return string
     */
    public function getTemplateFullPath()
    {
        return $this->getTemplateBasePath() . $this->getTemplate();
    }

    /**
     * Sets payment method active state
     *
     * @param boolean $blActive
     */
    public function setActive($blActive)
    {
        $this->blActive = $blActive;
    }

    /**
     * Returns payment method active state
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->blActive;
    }

    /**
     * Sets payment method active state
     *
     * @param string
     */
    public function setMode($sMode)
    {
        $this->sMode = $sMode;
    }

    /**
     * Returns mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->sMode;
    }

    /**
     * Sets payment request type
     *
     * @param string $sType
     */
    public function setRequestType($sType)
    {
        $this->sActRequestType = $sType;
    }

    /**
     * Returns payment request type
     *
     * @return string
     */
    public function getRequestType()
    {
        return $this->sActRequestType;
    }

    /**
     * Returns array with possible request types
     *
     * @return array
     */
    public function getRequestTypes()
    {
        return $this->aRequestTypes;
    }

    /**
     * Returns array with whitelisted iso codes
     *
     * @return array
     */
    public function getCountryWhitelist()
    {
        return $this->aCountryWhitelist;
    }

    /**
     * Returns array with blacklisted iso codes
     *
     * @return array
     */
    public function getCountryBlacklist()
    {
        return $this->aCountryBlacklist;
    }

    /**
     * Returns true if account can be settled
     *
     * @return bool
     */
    public function isAccountSettlementAllowed()
    {
        return $this->blAllowAccountSettlement;
    }

    /**
     * Returns true if payment needs bank data for debit
     * request
     *
     * @return bool
     */
    public function isBankDataNeededForDebit()
    {
        return $this->blNeedBankDataForDebit;
    }

    /**
     * Returns true if country is valid for payment
     * @param string $sCheckIsoCode
     * @return boolean
     */
    public function isValidCountry($sCheckIsoCode)
    {

        if (!$sCheckIsoCode) {
            return false;
        }

        if (($aWhitelist = $this->getCountryWhitelist()) && count($aWhitelist) > 0) {
            if (!in_array($sCheckIsoCode, $aWhitelist)) {
                return false;
            }
        } elseif (($aBlacklist = $this->getCountryBlacklist()) && count($aBlacklist) > 0) {
            if (in_array($sCheckIsoCode, $aBlacklist)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns base class name
     * notice: cant use __CLASS__, always returns base class
     * @return string
     */
    protected function getBaseClassName()
    {
        return str_replace('FcPayone', '', get_class($this));
    }

    /**
     * Builds and returns class name
     *
     * @param string $sBuildClass
     *
     * @return string
     */
    protected function buildClassName($sBuildClass)
    {
        return $sBuildClass . (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Returns backend form class
     *
     * @return string
     */
    public function getBackendFormClass()
    {
        return $this->buildClassName('Payone\Forms\Backend\Payment\\');
    }

    /**
     * Returns frontend form class
     *
     * @return string
     */
    public function getFrontendFormClass()
    {
        return $this->buildClassName('Payone\Forms\Frontend\Payment\\');
    }

    /**
     * Returns validation class
     *
     * @return string
     */
    public function getValidationClass()
    {
        return $this->buildClassName('Payone\Validation\Payment\\');
    }

    /**
     * Returns request builder class
     *
     * @return string
     */
    public function getRequestBuilderClass()
    {
        return $this->buildClassName('Payone\Request\Builder\Payment\\');
    }

    /**
     * Returns true if payment has sub payments
     *
     * @return boolean
     */
    public function hasSubPayments()
    {
        return $this->blHasSubPayments;
    }

    /**
     * Returns list with sub payments
     *
     * @return array
     */
    public function getSubPayments()
    {
        if ($this->aSubPayments == null) {
            $this->aSubPayments = array();
            $aSubPayments = Registry::getPayment()->getSubPaymentMethods((new \ReflectionClass($this))->getShortName());
            if (is_array($aSubPayments)) {
                $this->aSubPayments = $aSubPayments;
            }
        }
        return $this->aSubPayments;
    }

    /**
     * Returns list with sub payments
     *
     * @return array
     */
    public function getValidSubPayments()
    {
        $aPayments = $this->getSubPayments();
        if (!is_array($aPayments) || count($aPayments) == 0) {
            return;
        }

        $aValidPayments = array();
        foreach ($aPayments as $sKey => $oPayment) {
            if ($oPayment->isValidForCheckout()) {
                $aValidPayments[$sKey] = $oPayment;
            }
        }
        return $aValidPayments;
    }

    /**
     * Returns valid sub payment object
     *
     * @param string $sId
     * @return object
     */
    public function getValidSubPayment($sId)
    {
        $aSubPayments = $this->getValidSubPayments();
        if (is_array($aSubPayments)) {
            foreach ($aSubPayments as $oSubPayment) {
                if ($oSubPayment->getId() == $sId) {
                    return $oSubPayment;
                }
            }
        }
    }

    /**
     * Checks if payment is valid for checkout
     *
     * @return boolean
     */
    public function isValidForCheckout()
    {
        $oValidation = new Validation();
        if ($this->isActive() && $oValidation->validatePaymentCountry($this->getId())) {
            return true;
        }
        return false;
    }

    /**
     * Returns frontend controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->sController;
    }

    /**
     * Returns true if payment is an grouped payment
     * type
     *
     * @return bool
     */
    public function isGroupedPayment()
    {
        return $this->blIsGroupedPayment;
    }

    /**
     * Returns true if amount input is disabled
     * in order backend capture/redunf
     *
     * @return bool
     */
    public function isAmountInputDisabled()
    {
        return $this->blDisableAmountInput;
    }

    /**
     * Returns true if items should be added to capture request
     *
     * @return bool
     */
    public function isItemsRequiredInCaptureRequest()
    {
        return $this->blIsItemsRequiredInCaptureRequest;
    }

    /**
     * Returns true if items should be added to debit/refund request
     *
     * @return bool
     */
    public function isItemsRequiredInDebitRequest()
    {
        return $this->blIsItemsRequiredInDebitRequest;
    }
}
