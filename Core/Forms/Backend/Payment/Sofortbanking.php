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

class Sofortbanking extends OnlineTransfer
{
    /**
     * Returns form fields for sofort form
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
                        'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_SHOW_IBANBIC'),
                        'name' => 'FC_PAYONE_PAYMENT_SHOW_IBANBIC_' . $this->getPayment()->getId(true),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_IBANBIC_ON',
                                'value' => true,
                            ),
                            array(
                                'id' => 'FC_PAYONE_BACKEND_PAYMENT_SHOW_IBANBIC_OFF',
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
