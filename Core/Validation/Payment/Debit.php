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

namespace Payone\Validation\Payment;

use Payone\Forms\Frontend\Frontend as PayoneFrontendForm;

class Debit extends Base
{

    /**
     * Hook for payment validation
     *
     */
    protected function isValid()
    {
        parent::isValid();
        $this->validateForm();
        $this->validateMandate();
    }

    /**
     * Validates debit form
     */
    protected function validateForm()
    {
        $oForm = new PayoneFrontendForm();
        $aData = $oForm->getFormData($this->getValidationPayment());

        if ($aData['bankdatatype'] == 1) {
            $this->validateIban($aData['iban']);
            $this->validateBic($aData['bic']);
        } else {
            $this->validateBankAccount($aData['bankaccount']);
            $this->validateBankCode($aData['bankcode']);
        }
        //$this->validateBankAccountHolder($aData['bankaccountholder']);
    }

    /**
     * Check bank account
     *
     * @throws \Exception
     *
     * @param string $sString
     */
    public function validateBankAccount($sString)
    {
        if (!trim($sString) || !preg_match('/^[0-9]+$/', $sString) || \Tools::strlen($sString) > 10) {
            throw new \Exception('FC_PAYONE_ERROR_BANKACCOUNT_INVALID');
        }
    }

    /**
     * Check bank code
     * @throws \Exception
     * @param string $sString
     */
    public function validateBankCode($sString)
    {
        if (!trim($sString) || !preg_match('/^[0-9]+$/', $sString) || \Tools::strlen($sString) != 8) {
            throw new \Exception('FC_PAYONE_ERROR_BANKCODE_INVALID');
        }
    }

    /**
     * Check bank account holder
     * @throws \Exception
     * @param string $sString
     */
    public function validateBankAccountHolder($sString)
    {
        if (!trim($sString)) {
            throw new \Exception('FC_PAYONE_ERROR_BANKACCOUNTHOLDER_INVALID');
        }
    }

    /**
     * Validates mandate
     *
     * @throws \Exception
     *
     * @return boolean
     */
    protected function validateMandate()
    {
        $oFrontendForm = new PayoneFrontendForm();
        $oForm = $oFrontendForm->getFormObject($this->getValidationPayment());
        $oForm->getMandate();
        $aData = $oForm->getFormData();
        if ($oForm->getMandate() && $aData['mandate_accepted']) {
            return true;
        } elseif ($oForm->getMandate() && !$aData['mandate_accepted']) {
            throw new \Exception('FC_PAYONE_ERROR_MANDATE_NOT_ACCEPTED');
        }
    }
}
