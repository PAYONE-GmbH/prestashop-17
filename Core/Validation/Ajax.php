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

include_once(dirname(__FILE__) . '/../../../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../../../init.php');
require_once dirname(__FILE__) . '/../../fcpayone.php';

use Payone\Base\Registry;

$oModule = new FcPayone();

if (\Tools::getValue('payone_secure_key') != $oModule->secure_key) {
    echo \Tools::jsonEncode(array('errorMessages' => array('Secure key is not valid!')));
    exit;
}

class ValidationAjax
{

    /**
     * Response
     *
     * @var array
     */
    protected $aResponse = null;

    /**
     * Start validation
     */
    public function validate()
    {
        $sPaymentId = $this->getPaymentId();
        switch ($sPaymentId) {
            case 'debit':
                $this->validateDebit();
                break;
            case 'onlinetransfer':
                $this->validateOnlineTransfer();
                break;
        }

        echo \Tools::jsonEncode($this->aResponse);
    }

    /**
     * Returns submitted form
     *
     * @return array
     */
    protected function getForm()
    {
        return \Tools::getValue('fcpayone_form');
    }

    /**
     * Returns payment id
     *
     * @return string
     */
    protected function getPaymentId()
    {
        return \Tools::getValue('paymentid');
    }

    /**
     * Adds error to response
     *
     * @param array $sMessage
     */
    protected function addErrorMessageToResponse($sMessage)
    {
        $this->aResponse['errorMessages'][] = $sMessage;
    }

    /**
     * Add replacement
     *
     * @param string $sKey
     * @param string $sValue
     */
    protected function addReplacement($sKey, $sValue)
    {
        $this->aResponse['replacements'][$sKey] = $sValue;
    }

    /**
     * Load required debit validation files
     */
    protected function loadDebitFiles()
    {
        require_once dirname(__FILE__) . '/../Validation/Payment/Base.php';
        require_once dirname(__FILE__) . '/../Validation/Payment/Debit.php';
    }

    /**
     * validate debit data
     */
    protected function validateDebit()
    {
        $this->loadDebitFiles();
        $oValidation = new \Payone\Validation\Payment\Debit;

        $aForm = $this->getForm();
        if ($aForm['bankdatatype'] == 1) {
            $this->validateIban($oValidation);

            if (\Configuration::get('FC_PAYONE_PAYMENT_SHOW_BIC_DEBIT')) {
                $this->validateBic($oValidation);
            }
        } else {
            $this->validateBankAccount($oValidation);
            $this->validateBankCode($oValidation);
        }
    }

    /**
     * Returns clean iban
     *
     * @return string
     */
    protected function getCleanIban()
    {
        $aForm = $this->getForm();
        return \Tools::strtoupper(preg_replace('/\s/', '', $aForm['iban']));
    }

    /**
     * Returns clean bic
     *
     * @return string
     */
    protected function getCleanBic()
    {
        $aForm = $this->getForm();
        return \Tools::strtoupper(preg_replace('/\s/', '', $aForm['bic']));
    }

    /**
     * validates iban
     *
     * @param object $oValidation
     */
    protected function validateIban($oValidation)
    {
        try {
            $this->addReplacement('iban', $this->getCleanIban());
            $oValidation->validateIban($this->getCleanIban());
        } catch (\Exception $oE) {
            $this->addErrorMessageToResponse(Registry::getTranslator()->translate($oE->getMessage()));
        }
    }

    /**
     * validates iban
     *
     * @param object $oValidation
     */
    protected function validateBic($oValidation)
    {
        try {
            $this->addReplacement('bic', $this->getCleanBic());
            $oValidation->validateBic($this->getCleanBic());
        } catch (\Exception $oE) {
            $this->addErrorMessageToResponse(Registry::getTranslator()->translate($oE->getMessage()));
        }
    }

    /**
     * validates bank account
     *
     * @param object $oValidation
     */
    protected function validateBankAccount($oValidation)
    {
        try {
            $aForm = $this->getForm();
            $oValidation->validateBankAccount($aForm['bankaccount']);
        } catch (\Exception $oE) {
            $this->addErrorMessageToResponse(Registry::getTranslator()->translate($oE->getMessage()));
        }
    }

    /**
     * validates bank code
     *
     * @param object $oValidation
     */
    protected function validateBankCode($oValidation)
    {
        try {
            $aForm = $this->getForm();
            $oValidation->validateBankCode($aForm['bankcode']);
        } catch (\Exception $oE) {
            $this->addErrorMessageToResponse(Registry::getTranslator()->translate($oE->getMessage()));
        }
    }

    /**
     * Validates onlinetransfer
     */
    protected function validateOnlineTransfer()
    {
        $sPaymentId = $this->getPaymentId();
        if ($sPaymentId == 'onlinetransfer_sofortbanking' || $sPaymentId == 'onlinetransfer_giropay') {
            require_once dirname(__FILE__) . '/../Validation/Payment/Base.php';
            $oValidation = new \Payone\Validation\Payment\Base;
            $this->validateIban($oValidation);
            $this->validateBic($oValidation);
        }
    }
}

$oValidation = new \ValidationAjax();
$oValidation->validate();
exit;
