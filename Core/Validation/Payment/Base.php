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

class Base extends \Payone\Validation\Base
{

    /**
     * Payment object
     *
     * @var object
     */
    protected $oValidationPayment = null;

    /**
     * Returns  payment object for validation
     *
     * @return object
     */
    protected function getValidationPayment()
    {
        return $this->oValidationPayment;
    }

    /**
     * Sets payment for validation
     *
     * @param object $oPayment
     */
    public function setValidationPayment($oPayment)
    {
        $this->oValidationPayment = $oPayment;
    }

    /**
     * Starts validation
     */
    public function validate()
    {
        try {
            $this->isValid();
            return true;
        } catch (\Exception $oEx) {
            $this->setError('validation_payment', $oEx->getMessage(), true);
        }
        return false;
    }

    /**
     * Checks if payment is usable
     *
     * @throws \Exception
     * @return boolean
     */
    protected function canUsePayment()
    {
        if (!$this->getValidationPayment()->isValidForCheckout()) {
            throw new \Exception('FC_PAYONE_ERROR_PAYMENT_NOT_USABLE');
        }
    }

    /**
     * Hook for payment validation
     *
     */
    protected function isValid()
    {
        $this->canUsePayment();
    }

    /**
     * Check iban
     *
     * @throws \Exception
     *
     * @param string $sString
     */
    public function validateIban($sString)
    {
        if (!trim($sString) || !preg_match('/^[a-zA-Z0-9]+$/', $sString) || \Tools::strlen($sString) > 35) {
            throw new \Exception('FC_PAYONE_ERROR_IBAN_INVALID');
        }
    }

    /**
     * Check bic
     *
     * @throws \Exception
     *
     * @param string $sString
     */
    public function validateBic($sString)
    {
        $oPayment = $this->getValidationPayment();
        if (method_exists($oPayment, 'showBic') && !$oPayment->showBic()) {
            return;
        }

        if (!trim($sString) || !preg_match('/^[a-zA-Z0-9]+$/', $sString) || \Tools::strlen($sString) > 11) {
            throw new \Exception('FC_PAYONE_ERROR_BIC_INVALID');
        }
    }
}
