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

namespace Payone\Forms\Backend\Payment;

class PayPal extends Base
{
    /**
     * Returns form fields for connection form
     * TODO check use invoice address option,
     * can cause error because if not active
     * you can change your address in paypal and
     * it gets not updated
     *
     * @return array
     */
    public function getForm()
    {
        $aForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTitle(),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    $this->getFieldActive(),
                    $this->getFieldMode(),
                    $this->getFieldRequestType(),
                    $this->getFieldCountry(),
                    /* not implemented yet,

                    array(
                        'type' => 'switch',
                        'label' => Translator::translate('FC_PAYONE_BACKEND_PAYMENT_PAYPAL_USE_INVOICE_ADDRESS'),
                        'desc' => Translator::translate('FC_PAYONE_BACKEND_PAYMENT_PAYPAL_USE_INVOICE_ADDRESS_DESC'),
                        'name' => 'FC_PAYONE_PAYMENT_PAYPAL_USE_INVOICE_ADDRESS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'FC_PAYONE_PAYMENT_INVOICE_ADDRESS_ON',
                                'value' => true,
                            ),
                            array(
                                'id' => 'FC_PAYONE_PAYMENT_INVOICE_ADDRESS_OFF',
                                'value' => false,
                            )
                        ),
                    ),*/
                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );
        return $this->postProcess($aForm);
    }
}
