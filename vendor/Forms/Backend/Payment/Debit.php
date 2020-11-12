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

namespace Payone\Forms\Backend\Payment;

class Debit extends Base
{

    /**
     * Returns form fields
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
                    array(
                        'type' => 'switch',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_SHOW_BANKACCOUNT_DESC'),
                        'name' => 'FC_PAYONE_PAYMENT_SHOW_BANKACCOUNT_' . \Tools::strtoupper($this->getIdent()),
                        'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_SHOW_BANKACCOUNT'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_BANKACCOUNT_ON',
                                'value' => true,
                            ),
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_BANKACCOUNT_OFF',
                                'value' => false,
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        //'hint' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_SHOW_BIC_DESC'),
                        'name' => 'FC_PAYONE_PAYMENT_SHOW_BIC_' . \Tools::strtoupper($this->getIdent()),
                        'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_SHOW_BIC'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_BIC_ON',
                                'value' => true,
                            ),
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_BIC_OFF',
                                'value' => false,
                            )
                        ),
                    ),
                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );
        return $this->postProcess($aForm);
    }
}
