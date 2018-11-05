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
use Payone\Base\Registry;

class OnlineTransfer extends Base
{

    /**
     * Hook for payment validation
     *
     */
    protected function isValid()
    {
        parent::isValid();

        if ($this->isAfterRedirect() == false) {
            $this->validateForm();
        } else {
            $this->isValidUserAgent();
        }
    }

    /**
     * Validates iban/bix form
     */
    protected function validateForm()
    {
        $oForm = new PayoneFrontendForm();
        $oPayment = $this->getValidationPayment();
        $aData = $oForm->getFormData(Registry::getPayment()->getSelectedMainPaymentMethod());

        if ($oPayment->hasIbanBic()) {
            $this->validateIban($aData['iban']);
            $this->validateBic($aData['bic']);
        }
    }
}
